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

use Cache\Adapter\Doctrine\DoctrineCachePool;
use Doctrine\Common\Cache\RedisCache;

class DoctrineRedisFactory implements AdapterFactoryInterface
{
    public function createAdapter(array $options = [])
    {
        if (!class_exists('Cache\Adapter\Doctrine\DoctrineCachePool')) {
            throw new \LogicException('You must install the "cache/doctrine-adapter" package to use the "doctrine_redis" provider.');
        }

        // TODO validate the options with symfony options resolver
        // TODO get ip, port and protocol from options.

        $redis = new \Redis();
        $redis->connect('127.0.0.1', '6379');

        $client = new RedisCache();
        $client->setRedis($redis);

        return new DoctrineCachePool($client);
    }
}
