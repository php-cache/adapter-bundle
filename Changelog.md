# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release. 

## UNRELEASED

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

* If a DSN is provided to redis we make sure to select a database for you. 

## 0.3.4

No changelog before this version
