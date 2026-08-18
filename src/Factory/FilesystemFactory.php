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

use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\FilesystemOperator;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class FilesystemFactory extends AbstractAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\Adapter\Filesystem\FilesystemCachePool', 'packageName' => 'cache/filesystem-adapter'],
    ];

    /**
     * @param array{flysystem_service: FilesystemOperator} $config
     */
    public function getAdapter(array $config): CacheItemPoolInterface
    {
        return new FilesystemCachePool($config['flysystem_service']);
    }

    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['flysystem_service']);

        $resolver->setAllowedTypes('flysystem_service', ['string', FilesystemOperator::class]);
    }
}
