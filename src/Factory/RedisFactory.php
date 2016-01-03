<?php

namespace Cache\AdapterBundle\Factory;


use Cache\Adapter\Redis\RedisCachePool;
use Predis\Client;

class RedisFactory implements AdapterFactoryInterface
{
    public function createAdapter(array $options = array())
    {
        if (!class_exists('Cache\Adapter\Redis\RedisCachePool')) {
            throw new \LogicException('You must install the "cache/redis-adapter" package to use the "redis" provider.');
        }

        // TOOD validate the options with symfony options resolver
        // TODO get ip, port and protocol from options.
        $client = new Client('tcp:/127.0.0.1:6379');

        return new RedisCachePool($client);
    }
}