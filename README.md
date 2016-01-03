# Adapter Bundle
[![Build Status](https://travis-ci.org/php-cache/adapter-bundle.png?branch=master)](https://travis-ci.org/php-cache/adapter-bundle) 

This bundle helps you configurate and register PSR-6 cache services. The bundle uses Doctrine as cache implementation 
with help from [DoctrineAdapter] to make it PSR-6 compliant. 

### To Install

Run the following in your project root, assuming you have composer set up for your project
```sh
composer require cache/adapter-bundle
```

Add the bundle to app/AppKernel.php

```php
$bundles(
    // ...
    new Cache\AdapterBundle\CacheAdapterBundle(),
    // ...
);
```


### Configuration

```yaml
cache_adapter_doctrine:
  providers:
    acme_memcached:
      type: memcached
      persistent: true # Boolean or persistent_id
      namespace: mc
      hosts:
        - { host: localhost, port: 11211 }
    acme_redis:
      type: redis
      hosts:
        main:
          host: 127.0.0.1
          port: 6379
    acme_file_system_cache:
      type: file_system
      extension: '.fsc'
      directory: '%kernel.root_dir%/var/storage/fs_cache/'
    acme_php_file_cache:
      type: php_file
      extension: '.cache'
      directory: '%kernel.root_dir%/var/storage/'
    acme_array_cache:
      type: array
    acme_apc_cache:
      type: apc
      namespace: my_ns
```

### Usage

When using a configuration like below, you will get a service with the id `cache.provider.acme_apc_cache`.
```yaml
cache_adapter_doctrine:
  providers:
    acme_apc_cache:
      type: apc
      namespace: my_ns
```

Use the new service as any PSR-6 cache. 
 
``` php
/** @var CacheItemPoolInterface $cache */
$cache = $this->container->get('cache.provider.acme_apc_cache');
// Or
$cache = $this->container->get('cache'); // This is either the `default` provider, or the first provider in the config

/** @var CacheItemInterface $item */
$item = $cache->getItem('cache-key');
$item->set('foobar');
$item->expiresAfter(3600);
$cache->save($item);
```

[DoctrineAdapter]: https://github.com/php-cache/doctrine-adapter
