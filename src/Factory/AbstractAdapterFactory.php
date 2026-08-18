<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Factory;

use Cache\AdapterBundle\Exception\ConfigurationException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base implementation for adapter factories.
 *
 * Subclasses declare their dependencies in AbstractAdapterFactory::DEPENDENCIES and configure their options in
 * AbstractAdapterFactory::configureOptionResolver().
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class AbstractAdapterFactory implements AdapterFactoryInterface
{
    protected const DEPENDENCIES = [];

    /**
     * @param array<string, mixed> $config
     */
    abstract protected function getAdapter(array $config): CacheItemPoolInterface;

    public function createAdapter(array $options = []): CacheItemPoolInterface
    {
        $this->verifyDependencies();

        $resolver = new OptionsResolver();
        static::configureOptionResolver($resolver);
        $config = $resolver->resolve($options);

        return $this->getAdapter($config);
    }

    public static function validate(array $options, string $adapterName): void
    {
        static::verifyDependencies();

        $resolver = new OptionsResolver();
        static::configureOptionResolver($resolver);

        try {
            $resolver->resolve($options);
        } catch (\Exception $e) {
            $message = \sprintf(
                'Error while configuring adapter %s. Verify your configuration at "cache_adapter.providers.%s.options". %s',
                $adapterName,
                $adapterName,
                $e->getMessage()
            );

            throw new ConfigurationException($message, $e->getCode(), $e);
        }
    }

    /**
     * Make sure that the required classes are available.
     *
     * @throws \LogicException
     */
    protected static function verifyDependencies(): void
    {
        foreach (static::DEPENDENCIES as $dependency) {
            if (!class_exists($dependency['requiredClass'])) {
                throw new \LogicException(\sprintf('You must install the "%s" package to use the "%s" factory.', $dependency['packageName'], static::class));
            }
        }
    }

    /**
     * Configure the options accepted by the factory.
     */
    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
    }
}
