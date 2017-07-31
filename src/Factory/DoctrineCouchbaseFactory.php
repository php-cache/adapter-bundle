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

use Cache\Adapter\Doctrine\DoctrineCachePool;
use Couchbase;
use Doctrine\Common\Cache\CouchbaseCache;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DoctrineCouchbaseFactory extends AbstractDoctrineAdapterFactory
{
    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $couchbase = new Couchbase($config['host'], $config['user'], $config['password'], $config['bucket']);

        $client = new CouchbaseCache();
        $client->setCouchbase($couchbase);

        return new DoctrineCachePool($client);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'host'     => '127.0.0.1',
            'user'     => 'Administrator',
            'password' => 'password',
            'bucket'   => 'default',
        ]);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('user', ['string']);
        $resolver->setAllowedTypes('password', ['string']);
        $resolver->setAllowedTypes('bucket', ['string']);
    }
}
