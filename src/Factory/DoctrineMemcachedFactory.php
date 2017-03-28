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
use Doctrine\Common\Cache\MemcachedCache;
use Memcached;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DoctrineMemcachedFactory extends AbstractDoctrineAdapterFactory
{
    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $memcached = new Memcached();
        $memcached->addServer($config['host'], $config['port']);

        $client = new MemcachedCache();
        $client->setMemcached($memcached);

        return new DoctrineCachePool($client);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'host' => '127.0.0.1',
            'port' => '11211',
        ]);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
    }
}
