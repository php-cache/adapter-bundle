<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\DependencyInjection;

use Cache\AdapterBundle\DummyAdapter;
use Cache\AdapterBundle\Exception\ConfigurationException;
use Cache\AdapterBundle\Factory\AdapterFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
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
     * @param array<array-key, mixed> $configs   Array of configs
     * @param ContainerBuilder        $container Container Object
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Configure client services
        $first = isset($config['providers']['default']) ? 'default' : null;
        foreach ($config['providers'] as $name => $arguments) {
            if (null === $first) {
                $first = $name;
            }

            $factoryClass = $container->getDefinition($arguments['factory'])->getClass();
            if (!is_string($factoryClass) || !is_a($factoryClass, AdapterFactoryInterface::class, true)) {
                throw new ConfigurationException(sprintf('Service "%s" must use a factory implementing "%s".', $arguments['factory'], AdapterFactoryInterface::class));
            }

            $factoryClass::validate($arguments['options'], $name);

            // See if any option has a service reference
            $arguments['options'] = $this->findReferences($arguments['options']);

            $def = $container->register('cache.provider.'.$name, DummyAdapter::class);
            $def->setFactory([new Reference($arguments['factory']), 'createAdapter'])
                ->addArgument($arguments['options'])
                ->setPublic(true);

            $def->addTag('cache.provider');
            foreach ($arguments['aliases'] as $alias) {
                $container->setAlias($alias, new Alias('cache.provider.'.$name, true));
            }
        }

        if (null !== $first) {
            $defaultProvider = 'cache.provider.'.$first;
            if (null !== $config['fallback_provider']) {
                $fallbackProvider = ltrim($config['fallback_provider'], '@');
                if (array_key_exists($fallbackProvider, $config['providers'])) {
                    $fallbackProvider = 'cache.provider.'.$fallbackProvider;
                }
                $forbiddenProviders = ['cache', 'php_cache', 'cache.provider.default_fallback'];
                if (in_array($fallbackProvider, $forbiddenProviders, true)) {
                    throw new ConfigurationException('The fallback provider must differ from the default provider.');
                }
                $seenAliases = [];
                while ($container->hasAlias($fallbackProvider)) {
                    if (isset($seenAliases[$fallbackProvider])) {
                        throw new ConfigurationException('The fallback provider contains a circular alias.');
                    }
                    $seenAliases[$fallbackProvider] = true;
                    $fallbackProvider = (string) $container->getAlias($fallbackProvider);
                }
                if ($defaultProvider === $fallbackProvider || in_array($fallbackProvider, $forbiddenProviders, true)) {
                    throw new ConfigurationException('The fallback provider must differ from the default provider.');
                }

                $fallbackDefinition = $container->register('cache.provider.default_fallback', DummyAdapter::class);
                $fallbackDefinition
                    ->setFactory([new Reference('cache.factory.fallback'), 'createAdapter'])
                    ->addArgument(new ServiceClosureArgument(new Reference($defaultProvider)))
                    ->addArgument(new ServiceClosureArgument(new Reference($fallbackProvider)))
                    ->setPublic(true)
                    ->addTag('cache.provider');

                $defaultProvider = 'cache.provider.default_fallback';
                foreach ($config['providers'][$first]['aliases'] as $alias) {
                    $container->setAlias($alias, new Alias($defaultProvider, true));
                }
            }

            $container->setAlias('cache', $defaultProvider);
            $container->setAlias('php_cache', $defaultProvider);
        }
    }

    /**
     * @param array<array-key, mixed> $options
     *
     * @return array<array-key, mixed>
     */
    private function findReferences(array $options): array
    {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $options[$key] = $this->findReferences($value);
            } elseif (is_string($value) && (str_ends_with((string) $key, '_service') || str_starts_with($value, '@') || 'service' === $key)) {
                $options[$key] = new Reference(ltrim($value, '@'));
            }
        }

        return $options;
    }
}
