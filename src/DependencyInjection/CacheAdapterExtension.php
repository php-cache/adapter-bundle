<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\DependencyInjection;

use Cache\AdapterBundle\DummyAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CacheAdapterExtension extends Extension
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
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Configure client services
        $first = isset($config['providers']['default']) ? 'default' : null;
        foreach ($config['providers'] as $name => $arguments) {
            if ($first === null) {
                $first = $name;
            }

            $factoryClass = $container->getDefinition($arguments['factory'])->getClass();
            $factoryClass::validate($arguments['options'], $name);

            // See if any option has a service reference
            $arguments['options'] = $this->findReferences($arguments['options']);

            $def = $container->register('cache.provider.'.$name, DummyAdapter::class);
            $def->setFactory([new Reference($arguments['factory']), 'createAdapter'])
                ->addArgument($arguments['options']);

            $def->addTag('cache.provider');
            foreach ($arguments['aliases'] as $alias) {
                $container->setAlias($alias, 'cache.provider.'.$name);
            }
        }

        if ($first !== null) {
            $container->setAlias('php_cache', 'cache.provider.'.$first);
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function findReferences(array $options)
    {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $options[$key] = $this->findReferences($value);
            } elseif (substr($key, -8) === '_service' || strpos($value, '@') === 0 || $key === 'service') {
                $options[$key] = new Reference(ltrim($value, '@'));
            }
        }

        return $options;
    }
}
