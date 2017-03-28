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
use Doctrine\Common\Cache\MongoDBCache;
use MongoClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DoctrineMongodDBFactory extends AbstractDoctrineAdapterFactory
{
    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $mongo      = new MongoClient();
        $collection = $mongo->selectCollection($config['host'], $config['collection']);

        return new DoctrineCachePool(new MongoDBCache($collection));
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setRequired(['host', 'collection']);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('collection', ['string']);
    }

    protected static function verifyDependencies()
    {
        if (!version_compare(phpversion('mongo'), '1.3.0', '>=')) {
            throw new \LogicException('Mongo >= 1.3.0 is required.');
        }

        parent::verifyDependencies();
    }
}
