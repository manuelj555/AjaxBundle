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
use Ku\AjaxBundle\FlashHandler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class PrepareFlashesListener
{
    /**
     * @var AjaxHandler
     */
    private $ajaxHandler;

    /**
     * @var FlashHandler
     */
    private $flashHandler;

    /**
     * PrepareFlashesListener constructor.
     *
     * @param AjaxHandler $ajaxHandler
     * @param FlashHandler $flashHandler
     */
    public function __construct(AjaxHandler $ajaxHandler, FlashHandler $flashHandler)
    {
        $this->ajaxHandler = $ajaxHandler;
        $this->flashHandler = $flashHandler;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$request->isXmlHttpRequest() || $this->ajaxHandler->isIgnoredFlashes()) {
            return;
        }

        if ($response->isRedirection() || $response->getStatusCode() == 278) {
            /* Redirección normal o Ajax */
            return;
        }

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();

        if (!$session instanceof Session) {
            return;
        }

        $this->flashHandler->handle($session->getFlashBag(), $response);
    }
}