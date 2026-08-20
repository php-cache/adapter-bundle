# PHP Cache AdapterBundle

[![CI](https://github.com/php-cache/adapter-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/php-cache/adapter-bundle/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/cache/adapter-bundle/v/stable)](https://packagist.org/packages/cache/adapter-bundle)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

AdapterBundle creates PSR-6 cache services from Symfony configuration.

Version 2.1 requires PHP 8.2, `psr/cache` 3, `psr/log` 3, and Symfony 6.4, 7, or 8. It supports PHP Cache 2 and 3 adapters.

## Installation

Install the bundle and the adapter package your app needs:

```bash
composer require cache/adapter-bundle:^2.1 cache/redis-adapter:^3.0 cache/void-adapter:^3.0
```

Symfony Flex may register the bundle automatically. Otherwise, add it to `config/bundles.php`:

```php
return [
    Cache\AdapterBundle\CacheAdapterBundle::class => ['all' => true],
];
```

## Configuration

```yaml
cache_adapter:
  fallback_provider: void
  providers:
    default:
      factory: cache.factory.redis
      options:
        dsn: '%env(REDIS_URL)%'
        pool_namespace: application
      aliases:
        - app.cache

    void:
      factory: cache.factory.void
```

This example registers `cache.provider.default` and aliases a lazy fallback wrapper as `cache`, `php_cache`, and `app.cache`. If creating the default provider throws, the wrapper creates `cache.provider.void` instead. A full service ID such as `@cache.provider.void` is also accepted.

AdapterBundle matches a bare value against configured provider names first. Provider names that contain dots, such as `warm.tier`, therefore work without a service prefix.

The bundle follows service aliases before creating the wrapper. It rejects the default provider and aliases that lead back to it.

It also rejects `cache`, `php_cache`, `cache.provider.default_fallback`, and circular alias chains.

`fallback_provider` handles failures that occur while Symfony constructs the default provider. For failures raised later by cache operations, configure a Chain provider with `skip_on_failure: true` and place a Void provider last.

Version 2.1 provides APCu, Array, Chain, Filesystem, Memcache, Memcached, MongoDB, Namespaced, Predis, Prefixed, Redis, and Void factories for PHP Cache 2 and 3.

Applications can use a custom service that implements `AdapterFactoryInterface` as a provider factory. AdapterBundle validates the service after Symfony merges all extension definitions, so another app bundle can define it.

Memcached `driver_options` override the pool defaults. This configuration keeps the default server and uses the ASCII protocol:

```yaml
cache_adapter:
  providers:
    memcached:
      factory: cache.factory.memcached
      options:
        driver_options:
          Memcached::OPT_BINARY_PROTOCOL: false
```

`namespace` and `pool_namespace` must not be empty. Redis DSNs support both password-only authentication and ACL credentials such as `redis://alice:secret@cache.example:6379/0`. Percent-encode reserved characters in usernames and passwords.

Read the [complete AdapterBundle documentation](https://www.php-cache.com/en/latest/symfony/adapter-bundle/) for every factory and option.

## Using version 3 adapters

AdapterBundle 2.1 supports PHP Cache 3 adapters while it remains compatible with PHP Cache 2 adapters.

PHP Cache 3 stores a generation snapshot with each tagged item and a generation marker for each tag. Version 2 and 3 workers cannot safely share tagged cache storage.

Stop or drain all workers, clear each shared cache, and then deploy AdapterBundle 2.1 with version 3 adapters. Follow the same sequence before a rollback.

## Upgrading from version 1

Version 2 removes the APC factory and every Doctrine-backed factory. Use `cache.factory.apcu`, a supported native adapter, or an external PSR-6 service.

The Namespaced and Prefixed factories preserve native tag support. The Array, Memcached, Predis, and Redis `pool_namespace` options preserve it too. Code that constructs those decorators directly should use their `create()` factories when tagged items are required.

Replace MongoDB's `namespace` option with `database` and `collection`. Replace Predis's `schema` option with `scheme`.

PHP Cache 2 changes APCu payloads, Redis and Predis tag indexes, namespaced tag indexes, and hierarchy storage paths. Do not mix version 1 and version 2 workers on an affected store.

Clear a namespaced store when a namespace contains bytes outside `[A-Za-z0-9_.]` or lowercase `_x`. Also clear it when a public key contains `|`, `!`, or lowercase `_x`.

Clear namespaced stores containing tagged or hierarchy items. Clear a prefixed store when its prefix contains bytes outside `[A-Za-z0-9_.]` or lowercase `_x`.

Stop or drain old workers, clear each affected store, and then deploy version 2. Follow the same sequence before rolling back.

## Contributing

Run `composer quality` before opening a pull request. Report problems on the [GitHub issue tracker](https://github.com/php-cache/adapter-bundle/issues).
