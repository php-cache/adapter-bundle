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

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class FilesystemFactory extends AbstractAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Adapter\Filesystem\FilesystemCachePool', 'packageName' => 'cache/filesystem-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        return new FilesystemCachePool($config['flysystem_service']);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setRequired(['flysystem_service']);

        $resolver->setAllowedTypes('flysystem_service', ['string', 'object']);
    }
}
