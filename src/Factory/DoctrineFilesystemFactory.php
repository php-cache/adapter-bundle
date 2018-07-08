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
use Doctrine\Common\Cache\FilesystemCache;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DoctrineFilesystemFactory extends AbstractDoctrineAdapterFactory
{
    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $client = new FilesystemCache($config['directory'], $config['extension'], (int) $config['umask']);

        return new DoctrineCachePool($client);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'extension' => FilesystemCache::EXTENSION,
            'umask' => '0002',
        ]);

        $resolver->setRequired(['directory']);

        $resolver->setAllowedTypes('directory', ['string']);
        $resolver->setAllowedTypes('extension', ['string']);
        $resolver->setAllowedTypes('umask', ['string', 'int']);
    }
}
