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

use Cache\Adapter\Apc\ApcCachePool;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ApcFactory extends AbstractAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Adapter\Apc\ApcCachePool', 'packageName' => 'cache/apc-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        return new ApcCachePool();
    }
}
