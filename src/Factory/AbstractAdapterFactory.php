<?php

/*
 * This file is part of php-cache\adapter-bundle package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Factory;

use Cache\AdapterBundle\Exception\ConfigurationException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class AbstractAdapterFactory implements AdapterFactoryInterface
{
    protected static $dependencies = [];

    /**
     * @param array $config
     *
     * @return CacheItemPoolInterface
     */
    abstract protected function getAdapter(array $config);

    /**
     * {@inheritdoc}
     */
    public function createAdapter(array $options = [])
    {
        $this->verifyDependencies();

        $resolver = new OptionsResolver();
        static::configureOptionResolver($resolver);
        $config = $resolver->resolve($options);

        return $this->getAdapter($config);
    }

    /**
     * {@inheritdoc}
     */
    public static function validate(array $options = [], $adapterName)
    {
        static::verifyDependencies();

        $resolver = new OptionsResolver();
        static::configureOptionResolver($resolver);

        try {
            $resolver->resolve($options);
        } catch (\Exception $e) {
            $message = sprintf(
                'Error while configure adapter %s. Verify your options under "cache_adapter.providers.%s.options". %s',
                $adapterName,
                $adapterName,
                $e->getMessage()
            );

            throw new ConfigurationException($message, $e->getCode(), $e);
        }
    }

    /**
     * Make sure that we have the required class and throws and exception if we dont.
     *
     * @throws \LogicException
     */
    protected static function verifyDependencies()
    {
        foreach (static::$dependencies as $dependency) {
            if (!class_exists($dependency['requiredClass'])) {
                throw new \LogicException(
                    sprintf(
                        'You must install the "%s" package to use the "%s" factory.',
                        $dependency['packageName'],
                        static::class
                    )
                );
            }
        }
    }

    /**
     * By default we have not options ot configure. A factory should override this function and confgure
     * the options resolver.
     *
     * @param OptionsResolver $resolver
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
    }
}
