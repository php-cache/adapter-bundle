<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * This client is used as a placeholder for the dependency injection. It will never be used.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DummyAdapter implements CacheItemPoolInterface, LoggerAwareInterface
{
    public function getItem(string $key): CacheItemInterface
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    /**
     * @return iterable<string, CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    public function hasItem(string $key): bool
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    public function clear(): bool
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    public function deleteItem(string $key): bool
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    public function deleteItems(array $keys): bool
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    public function save(CacheItemInterface $item): bool
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    public function commit(): bool
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }

    public function setLogger(LoggerInterface $logger): void
    {
        throw new \LogicException('The dummy adapter cannot be used.');
    }
}
