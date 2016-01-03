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

use Cache\Adapter\Redis\RedisCachePool;
use Predis\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RedisFactory implements AdapterFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createAdapter(array $options = [])
    {
        if (!class_exists('Cache\Adapter\Redis\RedisCachePool')) {
            throw new \LogicException('You must install the "cache/redis-adapter" package to use the "redis" provider.');
        }

        $config = $this->configureOptions($options);
        $client = new Client(sprintf('%s://%s:%s', $config['protocol'], $config['host'], $config['port']));

        return new RedisCachePool($client);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function configureOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'host' => '127.0.0.1',
            'port' => '6379',
            'protocol' => 'tcp',
        ]);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('protocol', ['string']);

        return $resolver->resolve($options);
    }
}
