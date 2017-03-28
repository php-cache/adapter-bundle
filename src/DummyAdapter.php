<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * This client is used as a placeholder for the dependency injection. It will never be used.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DummyAdapter implements CacheItemPoolInterface
{
    public function getItem($key)
    {
    }

    public function getItems(array $keys = [])
    {
    }

    public function hasItem($key)
    {
    }

    public function clear()
    {
    }

    public function deleteItem($key)
    {
    }

    public function deleteItems(array $keys)
    {
    }

    public function save(CacheItemInterface $item)
    {
    }

    public function saveDeferred(CacheItemInterface $item)
    {
    }

    public function commit()
    {
    }
}
