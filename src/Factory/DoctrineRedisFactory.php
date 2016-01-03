<?php

namespace Cache\AdapterBundle\Factory;

use Cache\Adapter\Doctrine\DoctrineCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Doctrine\Common\Cache\RedisCache;
use Predis\Client;

class DoctrineRedisFactory implements AdapterFactoryInterface
{
    public function createAdapter(array $options = array())
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