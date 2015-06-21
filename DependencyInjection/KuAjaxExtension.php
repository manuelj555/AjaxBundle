<?php

namespace Ku\AjaxBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KuAjaxExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

//        $container->setParameter('ku_ajax.handler.stop_redirections', $config['handler']['stop_redirections']);

        $this->addFlashConfig($container, $config['flash_messages'], $loader);
    }

    protected function addFlashConfig(ContainerBuilder $container, $config, Loader\FileLoader $loader)
    {
        if(!$config['enabled']){
            return;
        }

        $loader->load('flash_messages.yml');

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        foreach ($config['mapping'] as $flashType => $definition) {
            $config['mapping'][$flashType]['type'] = $definition['type'] ?: $flashType;
        }

        $container->setParameter('ku_ajax.flash.mapping', $config['mapping']);

        if (!$config['auto_assets']['enabled']) {
            $container->removeDefinition('ku_ajax.flash.auto_assets_listener');
        } else {
            $definition = $container->findDefinition('ku_ajax.flash.auto_assets_listener');

            if ($config['auto_assets']['pnotify']['enabled']) {
                unset($config['auto_assets']['pnotify']['enabled']);
                $definition->addMethodCall('setPNotify', array(json_encode($config['auto_assets']['pnotify'])));
            }elseif($config['auto_assets']['sticky']['enabled']){
                unset($config['auto_assets']['sticky']['enabled']);
                $definition->addMethodCall('setSticky', array(json_encode($config['auto_assets']['sticky'])));
            }
        }
    }
}
