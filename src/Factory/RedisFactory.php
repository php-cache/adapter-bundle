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
class RedisFactory extends AbstractAdapterFactory
{
    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $client = new Client(sprintf('%s://%s:%s', $config['protocol'], $config['host'], $config['port']));

        return new RedisCachePool($client);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'host'     => '127.0.0.1',
            'port'     => '6379',
            'protocol' => 'tcp',
        ]);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('protocol', ['string']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredClass()
    {
        return 'Cache\Adapter\Redis\RedisCachePool';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageName()
    {
        return 'cache/redis-adapter';
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return 'redis';
    }
}
