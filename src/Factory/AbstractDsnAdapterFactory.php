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
    /**
     * @type DSN
     */
    private $DSN;

    /**
     * @return DSN
     */
    protected function getDsn()
    {
        return $this->DSN;
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['dsn' => '']);
        $resolver->setAllowedTypes('dsn', ['string']);
    }

    /**
     * {@inheritdoc}
     */
    public static function validate(array $options, $adapterName)
    {
        parent::validate($options, $adapterName);

        if (empty($options['dsn'])) {
            return;
        }

        $dsn = new DSN($options['dsn']);
        if (!$dsn->isValid()) {
            throw new \InvalidArgumentException('Invalid DSN: '.$options['dsn']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createAdapter(array $options = [])
    {
        if (!empty($options['dsn'])) {
            $dsn = new DSN($options['dsn']);
            if (!$dsn->isValid()) {
                throw new \InvalidArgumentException('Invalid DSN: '.$options['dsn']);
            }

            $this->DSN = $dsn;
        }

        return parent::createAdapter($options);
    }
}
