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

/**
 * @author Manuel Aguirre <programador.manuel@gmail.com>
 */
class AjaxHandler
{
    private $statusCode = 200;
    private $isOk = null;
    private $error;
    private $ignoreFlashes = false;

    /**
     * @param int $statusCode
     * @return AjaxHandler
     */
    public function success($statusCode = 200)
    {
        $this->isOk = true;
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param $message
     * @param int $statusCode
     * @return AjaxHandler
     */
    public function error($message, $statusCode = 400)
    {
        $this->isOk = false;
        $this->statusCode = $statusCode;
        $this->error = $message;

        return $this;
    }

    /**
     * @param int $statusCode
     * @return AjaxHandler
     */
    public function badRequest($statusCode = 400)
    {
        $this->isOk = false;
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param int $statusCode
     * @return AjaxHandler
     */
    public function redirect($statusCode = 278)
    {
        if (!$this->isHandled()) {
            throw new \BadFunctionCallException("Must be call to success or error method first");
        }

        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param bool $ignore
     * @return AjaxHandler
     */
    public function ignoreFlashes($ignore = true)
    {
        if (!$this->isHandled()) {
            throw new \BadFunctionCallException("Must be call to success or error method first");
        }

        $this->ignoreFlashes = $ignore;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHandled()
    {
        return null !== $this->isOk;
    }

    /**
     * @return AjaxHandler
     */
    public function resetHandler()
    {
        $this->isOk = null;
        $this->statusCode = 200;
        $this->error = null;
        $this->ignoreFlashes = false;

        return $this;
    }

    /**
     * @return null
     */
    public function isOk()
    {
        return $this->isOk;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return boolean
     */
    public function isIgnoredFlashes()
    {
        return $this->ignoreFlashes;
    }
}