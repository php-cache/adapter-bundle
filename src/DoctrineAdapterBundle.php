<?php

/*
 * This file is part of php-cache\doctrine-adapter-bundle package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Adapter\DoctrineAdapterBundle;

use Cache\Adapter\DoctrineAdapterBundle\DependencyInjection\DoctrineAdapterExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DoctrineAdapterBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new DoctrineAdapterExtension();
    }
}
