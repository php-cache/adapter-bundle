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

use Cache\Adapter\Doctrine\DoctrineCachePool;
use Doctrine\Common\Cache\ZendDataCache;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DoctrineZendDataFactory extends AbstractDoctrineAdapterFactory
{
    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        return new DoctrineCachePool(new ZendDataCache());
    }

    protected static function verifyDependencies()
    {
        if ('apache2handler' !== php_sapi_name()) {
            throw new \LogicException('Zend Data Cache only works in apache2handler SAPI.');
        }

        parent::verifyDependencies();
    }
}
