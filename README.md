# Adapter Bundle
[![Build Status](https://travis-ci.org/php-cache/adapter-bundle.png?branch=master)](https://travis-ci.org/php-cache/adapter-bundle) 

This bundle helps you configurate and register PSR-6 cache services. 

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
cache_adapter:
  providers:
    acme_memcached:
      factory: cache.factory.memcached
      options: 
        persistent: true # Boolean or persistent_id
        namespace: mc
        hosts:
          - { host: localhost, port: 11211 }      
```

### Usage

When using a configuration like below, you will get a service with the id `cache.provider.acme_redis`.
```yaml
cache_adapter:
  providers:
    acme_redis:
      factory: cache.factory.redis
```

Use the new service as any PSR-6 cache. 
 
``` php
/** @var CacheItemPoolInterface $cache */
$cache = $this->container->get('cache.provider.acme_redis');
// Or
$cache = $this->container->get('cache'); // This is either the `default` provider, or the first provider in the config

/** @var CacheItemInterface $item */
$item = $cache->getItem('cache-key');
$item->set('foobar');
$item->expiresAfter(3600);
$cache->save($item);
```

