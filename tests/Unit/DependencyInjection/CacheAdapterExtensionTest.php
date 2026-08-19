<?php

declare(strict_types=1);

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Tests\Unit\DependencyInjection;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\AdapterBundle\CacheAdapterBundle;
use Cache\AdapterBundle\DependencyInjection\CacheAdapterExtension;
use Cache\AdapterBundle\DummyAdapter;
use Cache\AdapterBundle\Exception\ConfigurationException;
use Cache\AdapterBundle\Factory\AbstractAdapterFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class CacheAdapterExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new CacheAdapterExtension(),
        ];
    }

    public function testThatProvidersExists()
    {
        $providers = ['foo' => ['factory' => 'cache.factory.array']];
        $this->load(['providers' => $providers]);

        $this->assertContainerBuilderHasService('cache.provider.foo', DummyAdapter::class);
        $this->assertContainerBuilderHasAlias('cache', 'cache.provider.foo');
        $this->assertContainerBuilderHasService('cache.factory.mongodb');
    }

    public function testItResolvesApplicationDefinedFactoryServicesAfterExtensionsMerge()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new CacheAdapterExtension());
        $container->registerExtension(new ApplicationCacheExtension());
        (new CacheAdapterBundle())->build($container);
        $container->loadFromExtension('cache_adapter', [
            'providers' => [
                'custom' => ['factory' => 'application.cache_factory'],
            ],
        ]);
        $container->loadFromExtension('application_cache');

        $container->compile();

        self::assertInstanceOf(ArrayCachePool::class, $container->get('cache.provider.custom'));
    }

    public function testAliasProvidersExists()
    {
        $providers = ['foo' => ['factory' => 'cache.factory.array', 'aliases' => ['alias_http']]];
        $this->load(['providers' => $providers]);

        $this->assertContainerBuilderHasService('cache.provider.foo', DummyAdapter::class);
        $this->assertContainerBuilderHasAlias('cache', 'cache.provider.foo');
        $this->assertContainerBuilderHasAlias('alias_http', 'cache.provider.foo');
    }

    public function testDefaultAliasProvidersExists()
    {
        $providers = [
            'foo' => ['factory' => 'cache.factory.array', 'aliases' => ['alias_foo']],
            'bar' => ['factory' => 'cache.factory.array', 'aliases' => ['alias_bar', 'alias_other']],
        ];
        $this->load(['providers' => $providers]);

        $this->assertContainerBuilderHasService('cache.provider.foo', DummyAdapter::class);
        $this->assertContainerBuilderHasAlias('cache', 'cache.provider.foo');
        $this->assertContainerBuilderHasAlias('alias_foo', 'cache.provider.foo');
        $this->assertContainerBuilderHasAlias('alias_bar', 'cache.provider.bar');
    }

    public function testItConvertsNestedServiceReferencesAndPreservesScalarOptions()
    {
        $providers = [
            'foo' => ['factory' => 'cache.factory.array'],
            'chain' => [
                'factory' => 'cache.factory.chain',
                'options' => [
                    'services' => ['@cache.provider.foo'],
                    'skip_on_failure' => true,
                ],
            ],
        ];
        $this->load(['providers' => $providers]);

        $options = $this->container->getDefinition('cache.provider.chain')->getArgument(0);

        self::assertIsArray($options);
        self::assertIsArray($options['services']);
        self::assertEquals(new Reference('cache.provider.foo'), $options['services'][0]);
        self::assertTrue($options['skip_on_failure']);
    }

    public function testFallbackProviderWrapsTheDefaultCacheAliases()
    {
        $this->load([
            'fallback_provider' => '@cache.provider.void',
            'providers' => [
                'default' => [
                    'factory' => 'cache.factory.array',
                    'aliases' => ['app.cache'],
                ],
                'void' => ['factory' => 'cache.factory.void'],
            ],
        ]);

        $definition = $this->container->getDefinition('cache.provider.default_fallback');
        $arguments = $definition->getArguments();

        self::assertEquals([new Reference('cache.factory.fallback'), 'createAdapter'], $definition->getFactory());
        self::assertInstanceOf(ServiceClosureArgument::class, $arguments[0]);
        self::assertEquals([new Reference('cache.provider.default')], $arguments[0]->getValues());
        self::assertInstanceOf(ServiceClosureArgument::class, $arguments[1]);
        self::assertEquals([new Reference('cache.provider.void')], $arguments[1]->getValues());
        self::assertSame('cache.provider.default_fallback', (string) $this->container->getAlias('cache'));
        self::assertSame('cache.provider.default_fallback', (string) $this->container->getAlias('php_cache'));
        self::assertSame('cache.provider.default_fallback', (string) $this->container->getAlias('app.cache'));
    }

    public function testFallbackProviderRejectsTheDefaultProvider()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('must differ from the default provider');

        $this->load([
            'fallback_provider' => '@cache.provider.default',
            'providers' => [
                'default' => ['factory' => 'cache.factory.array'],
            ],
        ]);
    }

    public function testFallbackProviderResolvesConfiguredNamesContainingDots()
    {
        $this->load([
            'fallback_provider' => 'warm.tier',
            'providers' => [
                'default' => ['factory' => 'cache.factory.array'],
                'warm.tier' => ['factory' => 'cache.factory.void'],
            ],
        ]);

        $arguments = $this->container->getDefinition('cache.provider.default_fallback')->getArguments();
        self::assertInstanceOf(ServiceClosureArgument::class, $arguments[1]);
        self::assertEquals([new Reference('cache.provider.warm.tier')], $arguments[1]->getValues());
    }

    public function testFallbackProviderRejectsAnAliasOfTheDefaultProvider()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('must differ from the default provider');

        $this->load([
            'fallback_provider' => 'app.cache',
            'providers' => [
                'default' => [
                    'factory' => 'cache.factory.array',
                    'aliases' => ['app.cache'],
                ],
            ],
        ]);
    }

    public function testFallbackProviderRejectsTheGlobalCacheAlias()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('must differ from the default provider');

        $this->load([
            'fallback_provider' => 'cache',
            'providers' => [
                'default' => ['factory' => 'cache.factory.array'],
            ],
        ]);
    }

    public function testFallbackProviderRejectsAnAliasOfTheFallbackWrapper()
    {
        $this->container->setAlias('fallback.loop', 'cache.provider.default_fallback');
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('must differ from the default provider');

        $this->load([
            'fallback_provider' => 'fallback.loop',
            'providers' => [
                'default' => ['factory' => 'cache.factory.array'],
            ],
        ]);
    }

    public function testItRejectsServicesThatAreNotAdapterFactories()
    {
        $this->registerService('not_a_factory', \stdClass::class);
        $this->load(['providers' => ['foo' => ['factory' => 'not_a_factory']]]);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('must use a factory implementing');

        (new CacheAdapterBundle())->build($this->container);
        $this->compile();
    }

    public function testItRejectsAnEmptyNamespaceBeforeTheProviderIsInstantiated()
    {
        $this->load([
            'providers' => [
                'foo' => [
                    'factory' => 'cache.factory.array',
                    'options' => ['pool_namespace' => ''],
                ],
            ],
        ]);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('pool_namespace');

        (new CacheAdapterBundle())->build($this->container);
        $this->compile();
    }
}

final class ApplicationCacheExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->register('application.cache_factory', ApplicationCacheFactory::class);
    }

    public function getAlias(): string
    {
        return 'application_cache';
    }
}

final class ApplicationCacheFactory extends AbstractAdapterFactory
{
    protected function getAdapter(array $config): CacheItemPoolInterface
    {
        return new ArrayCachePool();
    }
}
