<?php

namespace Cache\Adapter\DoctrineAdapterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class AequasiCacheExtension.
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class DoctrineAdapterExtension extends Extension
{
    /**
     * Loads the configs for Cache and puts data into the container.
     *
     * @param array            $configs   Array of configs
     * @param ContainerBuilder $container Container Object
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('cache_adapter_doctrine.providers', $config['providers']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'cache_adapter_doctrine';
    }
}
