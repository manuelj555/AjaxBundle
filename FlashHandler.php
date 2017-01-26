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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author maguirre <maguirre@developerplace.com>
 */
class FlashHandler
{
    protected $mapping = array();
    protected $domain;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * PrepareFlashesListener constructor.
     *
     * @param array $mapping
     * @param $domain
     * @param TranslatorInterface $translator
     */
    public function __construct(array $mapping, $domain, TranslatorInterface $translator)
    {
        $this->mapping = $mapping;
        $this->domain = $domain;
        $this->translator = $translator;

        $this->initialize();
    }

    public function handle(FlashBagInterface $flashBag, Response $response)
    {
        $flashes = $flashBag->all();

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

        $response->headers->set('X-Ajax-Flash', json_encode($formatted));
        $response->headers->set('X-Ajax-Flash-Config', json_encode($usedTypes));
    }

    private function initialize()
    {
        if ($this->domain) {
            foreach ($this->mapping as $key => $config) {
                $this->mapping[$key]['title'] = $this->translator->trans(
                    $config['title'],
                    array(),
                    $this->domain
                );
            }
        }
    }
}