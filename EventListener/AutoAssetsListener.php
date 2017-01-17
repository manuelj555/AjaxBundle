<?php
/**
 * 04/10/2014
 * upload
 */

namespace Ku\AjaxBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class AutoAssetsListener
{
    protected $pNotify;
    protected $sticky;

    /**
     * @param array $pNotify
     */
    public function setPNotify($pNotify)
    {
        $this->pNotify = $pNotify;
    }

    /**
     * @param mixed $sticky
     */
    public function setSticky($sticky)
    {
        $this->sticky = $sticky;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();

        if ($request->isXmlHttpRequest()) {
            return;
        }

        $this->inject($request, $response);
    }

    /**
     * Injects the js scripts into the given Response.
     *
     * @param Response $response A Response instance
     */
    protected function inject(Request $request, Response $response)
    {
        if (function_exists('mb_stripos')) {
            $posrFunction = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } else {
            $posrFunction = 'strripos';
            $substrFunction = 'substr';
        }

        $content = $response->getContent();

        if (false !== $posBody = $posrFunction($content, '</body>')) {

            $publicPath = $request->getBasePath();

            if ($this->pNotify) {
                list($css, $scripts) = $this->injectPNotify($publicPath);
            } elseif ($this->sticky) {
                list($css, $scripts) = $this->injectSticky($publicPath);
            } else {
                $css = null;
                $scripts = "<script src=\"{$publicPath}/bundles/kuajax/js/plugin.js\"></script>";
            }

            $content = $substrFunction($content, 0, $posBody)
                . $scripts . $substrFunction($content, $posBody);

            $posHead = $posrFunction($content, '</head>');

            $content = $substrFunction($content, 0, $posHead)
                . $css . $substrFunction($content, $posHead);

            $response->setContent($content);
        }
    }

    protected function injectSticky($basePath)
    {
        $css
            = <<<HTML
<link href="{$basePath}/bundles/kuajax/vendor/sticky/styles/sticky.min.css" type="text/css" rel="stylesheet" media="screen"/>
HTML;

        $extra = trim($this->sticky, '{}[]');
        $scripts
            = <<<HTML
<script src="{$basePath}/bundles/kuajax/vendor/sticky/sticky.min.js"></script>
<script src="{$basePath}/bundles/kuajax/js/plugin.js"></script>
<script>
jQuery(function($){
    $.ajaxFlash('*', function(message, type, title, icon){
        message = title ? '<b>' + title + '</b><br>' + message : message;
        $.sticky(message, {
            stickyClass: type,
            title: title,
            icon: icon,
            {$extra}
        });
    });
});
</script>
HTML;

        return array($css, $scripts);
    }

    protected function injectPNotify($basePath)
    {
        $css
            = <<<HTML
<link href="{$basePath}/bundles/kuajax/vendor/pnotify/pnotify.custom.min.css" type="text/css" rel="stylesheet" media="screen"/>
HTML;

        $extra = trim($this->pNotify, '{}[]');

        $scripts
            = <<<HTML
<script src="{$basePath}/bundles/kuajax/vendor/pnotify/pnotify.custom.min.js"></script>
<script src="{$basePath}/bundles/kuajax/js/plugin.js"></script>
<script>
jQuery(function($){
    $.ajaxFlash('*', function(message, type, title, icon){
        new PNotify({
            type: type,
            text: message,
            title: title,
            icon: icon,
            {$extra}
        });
    });
});
</script>
HTML;

        return array($css, $scripts);
    }
}
