# Changelog

Each release groups changes under Added, Removed, Changed, or Fixed headings.

## 2.0.0

### Added

- Support for PHP 8.2 and later, Symfony 6.4 through 8, and PSR Cache and PSR Log 3.
- MongoDB database and collection options.
- PHPStan level 9 and the Symfony PHP-CS-Fixer rules.
- A lazy `fallback_provider` for default-provider construction failures.

### Changed

- Require PHP Cache 2 packages.
- Support Flysystem 2 and 3, MongoDB library 2, and Predis 2 and 3.
- Use current Symfony dependency injection and configuration APIs.
- Preserve native tag support in Namespaced and Prefixed providers.

### Fixed

- Resolve dotted `fallback_provider` names as configured providers before treating them as service IDs.
- Reject fallback aliases that lead to the default provider, the fallback wrapper, or a circular alias chain.
- Preserve tag APIs when Array, Memcached, Predis, or Redis uses `pool_namespace`.
- Reject empty `namespace` and `pool_namespace` options while validating configuration.
- Pass Redis ACL usernames to phpredis and decode percent-encoded DSN credentials.

### Removed

- The APC factory. Use the APCu factory instead.
- Every Doctrine-backed factory.
- Support for PHP versions below 8.2, Symfony versions below 6.4, and PSR Cache versions below 3.

## 1.3.1

### Fixed
- Fixed container compilation error when using Flysystem #86.

## 1.3.0

### Added

- Support for PHP7.3
- Support for Symfony 4.2
- Better auto wire support

## 1.2.0

### Added

- Allow passing options to the Memcached provider.
- Support autowiring for providers.

## 1.1.0

### Changed

- The `DummyAdapter` implements `LoggerAwareInterface`

## 1.0.0

### Added

- Support for Predis pool to be persistent
- Added the `php_cache` alias for the default provider.

### Removed

- Support for PHP 5.5

## 0.5.0

### Added

- Support for NamespacedCache
- Support for PrefixedCache
- Tests

### Changed

- All factories are final

## 0.4.0

### Added

* Added option `redudant_servers`

## 0.3.5

### Added

* `ConnectException` that is thrown when you fail to connect to Redis
* Support for using the `NamespacedCachePool`

### Fixed

* Select the configured Redis database when a DSN includes one.

## 0.3.4

No changelog before this version
