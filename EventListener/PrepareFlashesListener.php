<?php
/**
 * 03/10/2014
 * open-skool
 */

namespace Ku\AjaxBundle\EventListener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class PrepareFlashesListener
{
    protected $mapping = array();

    function __construct($mapping)
    {
        $this->mapping = $mapping;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if(!$event->getRequest()->isXmlHttpRequest()){
            return;
        }

        if (!$event->getRequest()->hasSession()) {
            return;
        }

        $session = $event->getRequest()->getSession();

        if (!$session instanceof Session) {
            return;
        }

        $flashes = $session->getFlashBag()->all();

        if (!count($flashes)) {
            return;
        }

        $formatted = array();
        $usedTypes = array();

        foreach ($flashes as $type => $messages) {
            if (isset($this->mapping[$type])) {
                $formatted[$this->mapping[$type]['type']] = $messages;
                $usedTypes[$this->mapping[$type]['type']] = $this->mapping[$type];
            } else {
                $formatted[$type] = $messages;
            }
        }

        $event->getResponse()
            ->headers
            ->set('X-Ajax-Flash', json_encode($formatted));
        $event->getResponse()
            ->headers
            ->set('X-Ajax-Flash-Config', json_encode($usedTypes));
    }
} 