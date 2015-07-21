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

    function __construct($ajaxHandler, $twig)
    {
        $this->ajaxHandler = $ajaxHandler;
        $this->twig = $twig;
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

        $response = $event->getResponse();

        $statusCode = $this->ajaxHandler->getStatusCode();
        $errors = $this->ajaxHandler->getError();

        $response->headers->set('X-Real-Status-Code', $response->getStatusCode());
        $response->setStatusCode($statusCode, $statusCode == 278 ? 'Ajax Redirect' : null);

        $response->headers->set('X-Ajax-Handler', true);
        $response->headers->set('X-Ajax-Ok', $this->ajaxHandler->isOk());

        if (!$this->ajaxHandler->isOk() and $errors) {
            if (is_array($errors)) {
                $response->setContent(json_encode($errors));
                $response->headers->set('Content-Type', 'application/json');
            } elseif ($errors instanceof \Traversable) {
                $response->setContent(json_encode(iterator_to_array($errors)));
                $response->headers->set('Content-Type', 'application/json');
            } else {
                $response->setContent((string) $errors);
            }
        }
    }
}