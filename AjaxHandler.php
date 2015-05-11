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
    private $triggers = array();

    public function trigger($eventName, $data)
    {
        $this->triggers['event'][] = array($eventName, $data);

        return $this;
    }

    public function redirect($success = true, $stopRedirection = true)
    {
        $this->triggers['redirect'] = array($success, $stopRedirection);

        return $this;
    }

    public function stopRedirect()
    {
        $this->triggers['stopRedirect'] = true;

        return $this;
    }

    public function errors($errors, $inHtml = true, $statusCode = 400)
    {
        $this->triggers['errors'] = array((array) $errors, $inHtml, $statusCode);

        return $this;
    }

    public function formErros(FormInterface $form, $inHtml = true, $statusCode = 400)
    {
        $this->triggers['formErrors'] = array($form, $inHtml, $statusCode);

        return $this;
    }

    public function closeModal($success = true)
    {
        $this->triggers['closeModal'] = $success;

        return $this;
    }

    public function isHandled()
    {
        return count($this->triggers) > 0;
    }

    public function resetHandler()
    {
        $this->triggers = array();
    }

    public function getTriggers()
    {
        return $this->triggers;
    }
}