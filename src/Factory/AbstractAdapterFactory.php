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

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class AbstractAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    abstract protected function configureOptionResolver(OptionsResolver $resolver);

    /**
     * @param array $config
     *
     * @return CacheItemPoolInterface
     */
    abstract protected function getAdapter(array $config);

    /**
     * Get the class name that is required for this adapter. This should more often than not be the cache pool.
     *
     * @return string
     */
    abstract protected function getRequiredClass();

    /**
     * Get the name of the package where require class lives.
     *
     * @return string
     */
    abstract protected function getPackageName();

    /**
     * Get the name of the adapter.
     *
     * @return string
     */
    abstract protected function getName();

    /**
     * {@inheritdoc}
     */
    public function createAdapter(array $options = [])
    {
        $this->verifyDependencies();

        $resolver = new OptionsResolver();
        $this->configureOptionResolver($resolver);
        $config = $resolver->resolve($options);

        return $this->getAdapter($config);
    }

    /**
     * Make sure that we have the required class and throws and exception if we dont.
     *
     * @throws \LogicException
     */
    protected function verifyDependencies()
    {
        if (!class_exists($this->getRequiredClass())) {
            throw new \LogicException(
                sprintf(
                    'You must install the "%s" package to use the "%s" provider.',
                    $this->getPackageName(),
                    $this->getName()
                )
            );
        }
    }
}
