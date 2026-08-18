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

use Cache\AdapterBundle\DSN;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
abstract class AbstractDsnAdapterFactory extends AbstractAdapterFactory
{
    private ?DSN $dsn = null;

    protected function getDsn(): ?DSN
    {
        return $this->dsn;
    }

    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['dsn' => '']);
        $resolver->setAllowedTypes('dsn', ['string']);
    }

    public static function validate(array $options, string $adapterName): void
    {
        parent::validate($options, $adapterName);

        $dsnValue = $options['dsn'] ?? '';
        if (!\is_string($dsnValue) || '' === $dsnValue) {
            return;
        }

        $dsn = new DSN($dsnValue);
        if (!$dsn->isValid()) {
            throw new \InvalidArgumentException('Invalid DSN: '.$dsnValue);
        }
    }

    public function createAdapter(array $options = []): \Psr\Cache\CacheItemPoolInterface
    {
        $this->dsn = null;
        $dsnValue = $options['dsn'] ?? '';
        if (\is_string($dsnValue) && '' !== $dsnValue) {
            $dsn = new DSN($dsnValue);
            if (!$dsn->isValid()) {
                throw new \InvalidArgumentException('Invalid DSN: '.$dsnValue);
            }

            $this->dsn = $dsn;
        }

        return parent::createAdapter($options);
    }
}
