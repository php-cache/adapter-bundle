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
use Doctrine\Common\Cache\RiakCache;
use Riak\Bucket;
use Riak\Connection;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DoctrineRiakFactory extends AbstractDoctrineAdapterFactory
{
    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $connection = new Connection($config['host'], $config['port']);
        $bucket = new Bucket($connection, $config['type']);

        return new DoctrineCachePool(new RiakCache($bucket));
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'host' => '127.0.0.1',
            'port' => '8087',
            'type' => null,
        ]);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('type', ['string', 'null']);
    }
}
