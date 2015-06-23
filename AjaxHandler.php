<?php
/*
 * This file is part of the Manuel Aguirre Project.
 *
 * (c) Manuel Aguirre <programador.manuel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ku\AjaxBundle;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Manuel Aguirre <programador.manuel@gmail.com>
 */
class AjaxHandler
{
    private $statusCode = 200;
    private $isOk = null;
    private $error;

    public function success($statusCode = 200)
    {
        $this->isOk = true;
        $this->statusCode = $statusCode;

        return $this;
    }

    public function error($message, $statusCode = 400)
    {
        $this->isOk = false;
        $this->statusCode = $statusCode;
        $this->error = $message;

        return $this;
    }

    public function redirect($isOk = true, $statusCode = 278)
    {
        if(!$this->isHandled()){
            throw new \BadFunctionCallException("Must be call to success or error method first");
        }

        $this->statusCode = $statusCode;

        return $this;
    }

    public function isHandled()
    {
        return null !== $this->isOk;
    }

    public function resetHandler()
    {
        $this->isOk = null;
        $this->statusCode = 200;
        $this->error = null;

        return $this;
    }

    public function isOk()
    {
        return $this->isOk;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}