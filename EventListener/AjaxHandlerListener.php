<?php
/*
 * This file is part of the Manuel Aguirre Project.
 *
 * (c) Manuel Aguirre <programador.manuel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ku\AjaxBundle\EventListener;

use Ku\AjaxBundle\AjaxHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * @author Manuel Aguirre <programador.manuel@gmail.com>
 */
class AjaxHandlerListener implements EventSubscriberInterface
{
    /**
     * @var AjaxHandler
     */
    private $ajaxHandler;
    /**
     * @var \Twig_Environment
     */
    private $twig;
    private $stopRedirects;

    function __construct($ajaxHandler, $twig, $stopRedirects)
    {
        $this->ajaxHandler = $ajaxHandler;
        $this->twig = $twig;
        $this->stopRedirects = $stopRedirects;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }

    public function onKernelController()
    {
        $this->ajaxHandler->resetHandler();
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()
            or !$event->getRequest()->isXmlHttpRequest()
            or !$this->ajaxHandler->isHandled()
        ) {
            return;
        }

        $triggers = $this->ajaxHandler->getTriggers();

        foreach ($triggers as $name => $data) {
            $method = $name . 'Trigger';
            $this->{$method}($data, $event);
        }

        $response = $event->getResponse();

        if ($this->stopRedirects) {
            if ($response->headers->has('Location')) {
                $response->headers->remove('Location');
            }
        }
    }

    private function eventTrigger($triggers, FilterResponseEvent $event)
    {
        $event->getResponse()->headers->set('X-Ajax-Triggers', json_encode($triggers));
    }

    private function redirectTrigger($data, FilterResponseEvent $event)
    {
        list($success, $stopRedirection) = $data;

        $response = $event->getResponse();

        if ($response->headers->has('Location') or $response instanceof RedirectResponse) {

            $response->setContent($response->headers->get('Location'));

            $response->headers->set('X-Ajax-Redirect', json_encode(array(
                'success' => $success,
                'url' => $response->headers->get('Location'),
            )));

            if ($stopRedirection) {
                $status = $response->getStatusCode();
                $this->stopRedirectTrigger($data, $event);
                //volvemos a establecer el status code anterior
                $response->setStatusCode($status);
            }

        }

    }

    private function errorsTrigger($data, FilterResponseEvent $event)
    {
        list($errors, $inHtml, $statusCode) = $data;

        $headerData = array(
            'errors' => $errors,
            'is_html' => $inHtml,
        );

        if ($inHtml) {
            $errors = $this->twig->render('@KuAjax/errors.html.twig', array(
                'errors' => $errors,
            ));
        }

        $jsonResponse = new JsonResponse($errors, $statusCode, $event->getResponse()->headers->all());

        $jsonResponse->headers->set('X-Ajax-Errors', json_encode(array(
            'is_html' => $inHtml,
        )));

        $event->setResponse($jsonResponse);
    }

    private function formErrorsTrigger($data, FilterResponseEvent $event)
    {
        /** @var FormInterface $form */
        list($form, $inHtml, $statusCode) = $data;

        if (!$form->isSubmitted() or $form->isValid()) {
            return;
        }

        $errors = array();
        $this->parseFormErrors($form->createView(), $errors);

        if ($inHtml) {
            foreach ($errors as $id => $e) {
                $errors[$id] = $this->twig->render('@KuAjax/form_errors.html.twig', array(
                    'errors' => $e,
                    'id' => $id,
                ));

            }
        }

        $jsonResponse = new JsonResponse($errors, $statusCode, $event->getResponse()->headers->all());

        $jsonResponse->headers->set('X-Ajax-Form-Errors', json_encode(array(
            'is_html' => $inHtml,
        )));

        $event->setResponse($jsonResponse);
    }

    private function closeModalTrigger($data, FilterResponseEvent $event)
    {
        $event->getResponse()->setContent('X-Ajax-Close-Modal');
        $event->getResponse()->headers->set('X-Ajax-Close-Modal', json_encode(array(
            'success' => $data,
        )));
    }

    private function stopRedirectTrigger($data, FilterResponseEvent $event)
    {
        if ($event->getResponse()->headers->has('Location')) {
            $event->getResponse()->headers->remove('Location');
            $event->getResponse()->setStatusCode(Response::HTTP_OK);
        }
    }

    private function parseFormErrors(FormView $view, &$errors)
    {
        if (count($view->vars['errors'])) {
            $errors[$view->vars['id']] = array();
            foreach ($view->vars['errors'] as $error) {
                $errors[$view->vars['id']][] = $error->getMessage();
            }
        }
        foreach ($view->children as $child) {
            $this->parseFormErrors($child, $errors);
        }
    }
}