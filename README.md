# Doctrine Cache Bundle

This bundle registers PSR6 cache services that wraps the doctrine cache. 

## Configuration and usage

```yaml

cache_adapter_doctrine:
  providers:
    acme_file_system_cache:
      extension: '.fsc'
      directory: '%kernel.root_dir%/var/storage/fs_cache/'
      type: file_system
    acme_apc_cache:
      type: apc
      namespace: my_ns
```

``` php

/** @var CacheItemPoolInterface $cacheProvider */
$cacheProvider = $this->container->get('cache.provider.acme_apc_cache');

/** @var CacheItemInterface $item */
$item = $cacheProvider->getItem('cache-key');

```