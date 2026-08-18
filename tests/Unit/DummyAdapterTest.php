<?php

declare(strict_types=1);

namespace Cache\AdapterBundle\Tests\Unit;

use Cache\AdapterBundle\DummyAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

final class DummyAdapterTest extends TestCase
{
    public function testItImplementsThePsrCacheContract()
    {
        self::assertInstanceOf(CacheItemPoolInterface::class, new DummyAdapter());
    }

    public function testEveryOperationRejectsDirectUse()
    {
        $adapter = new DummyAdapter();
        $item = $this->createStub(CacheItemInterface::class);
        $logger = $this->createStub(LoggerInterface::class);
        $operations = [
            static fn () => $adapter->getItem('key'),
            static fn () => $adapter->getItems(['key']),
            static fn () => $adapter->hasItem('key'),
            static fn () => $adapter->clear(),
            static fn () => $adapter->deleteItem('key'),
            static fn () => $adapter->deleteItems(['key']),
            static fn () => $adapter->save($item),
            static fn () => $adapter->saveDeferred($item),
            static fn () => $adapter->commit(),
            static fn () => $adapter->setLogger($logger),
        ];

        foreach ($operations as $operation) {
            try {
                $operation();
                self::fail('The dummy adapter operation did not throw.');
            } catch (\LogicException $exception) {
                self::assertSame('The dummy adapter cannot be used.', $exception->getMessage());
            }
        }
    }
}
