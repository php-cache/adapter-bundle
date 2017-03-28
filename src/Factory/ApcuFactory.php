<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Factory;

use Cache\Adapter\Apcu\ApcuCachePool;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ApcuFactory extends AbstractAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Adapter\Apcu\ApcuCachePool', 'packageName' => 'cache/apcu-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        return new ApcuCachePool();
    }
}
