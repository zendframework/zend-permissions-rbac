# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.0.1 - 2018-08-20

### Added

- Nothing.

### Changed

- [#37](https://github.com/zendframework/zend-permissions-rbac/pull/37) changes
  the return type hints of `RoleInterface::getChildren()` and `RoleInterface::getParents()`
  from `array` to `iterable`. This is not a BC break thanks to [Iterable Type Variance](http://php.net/manual/en/language.types.iterable.php#language.types.iterable.variance)

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#39](https://github.com/zendframework/zend-permissions-rbac/pull/39) fixes
  `Role::getPermissions()` to return all the permissions, if more than one.

## 3.0.0 - 2018-03-22

### Added

- [#34](https://github.com/zendframework/zend-permissions-rbac/pull/34) adds
  checks for circular references in the role hierarchy when using the
  `Role::addChild()` and `Role::addParent()` methods.

- [#35](https://github.com/zendframework/zend-permissions-rbac/pull/35) adds
  the method `Role::getPermissions(bool $children = true)` for retrieving all
  permissions to the related role, including all child permissions when
  `$children` is boolean `true`.

- [#35](https://github.com/zendframework/zend-permissions-rbac/pull/35) adds
  the method `Rbac::getRoles()`, which returns all roles registered with the
  instance as a flat array of instances.

### Changed

- [#34](https://github.com/zendframework/zend-permissions-rbac/pull/34) updates
  the `Role::addChild(RoleInterface $child)` method to accept only a `RoleInterface` parameter;
  strings are no longer accepted.

- [#34](https://github.com/zendframework/zend-permissions-rbac/pull/34) updates
  the `Zend\Permissions\Rbac\AssertionInterface`, adding two parameters to the
  `assert()` definition and defining a return type, so that it now reads as
  follows:

  ```php
  public function assert(
      Rbac $rbac,
      RoleInterface $role,
      string $permission
  ) : bool
  ```

### Deprecated

- Nothing.

### Removed

- [#34](https://github.com/zendframework/zend-permissions-rbac/pull/34) removes
  support for PHP versions prior to 7.1.

- [#34](https://github.com/zendframework/zend-permissions-rbac/pull/34) removes
  the [AbstractIterator](https://github.com/zendframework/zend-permissions-rbac/blob/release-2.6.0/src/AbstractIterator.php)
  class. The role hierarchy no longer relies on a `RecursiveIterator`.

- [#34](https://github.com/zendframework/zend-permissions-rbac/pull/34) removes
  the [AbstractRole](https://github.com/zendframework/zend-permissions-rbac/blob/release-2.6.0/src/AbstractRole.php)
  class. All its functions have been merged to the `Zend\Permissions\Rbac\Role`
  class.

- [#34](https://github.com/zendframework/zend-permissions-rbac/pull/34) removes
  the method `Role::setParent()`; use `Role::addParent()` instead.

### Fixed

- [#30](https://github.com/zendframework/zend-permissions-rbac/issues/30) fixes
  circular references within the `Role::addChild()` and `Role::addParent()`
  algorithms.

## 2.6.0 - 2018-02-01

### Added

- [#12](https://github.com/zendframework/zend-permissions-rbac/pull/12) adds
  and publishes the documentation to https://zendframework.github.io/zend-permissions-rbac/

- [#23](https://github.com/zendframework/zend-permissions-rbac/pull/23) adds
  support for multiple parent roles, fixing an issue with reverse traversal of
  the inheritance tree. To accomplish this, the method `addParent($parent)` was
  added, and the method `getParent()` now can also return an array of roles.

- [#31](https://github.com/zendframework/zend-permissions-rbac/pull/31) adds
  support for PHP 7.2.

### Changed

- Nothing.

### Deprecated

- [#23](https://github.com/zendframework/zend-permissions-rbac/pull/23)
  deprecates the method `setParent()`. Use `addParent()` instead.

### Removed

- [#29](https://github.com/zendframework/zend-permissions-rbac/pull/29) removes
  support for PHP 5.5.

- [#29](https://github.com/zendframework/zend-permissions-rbac/pull/29) removes
  support for HHVM.

### Fixed

- [#21](https://github.com/zendframework/zend-permissions-rbac/pull/21) fixes
  dynamic assertion checking, adding the AND with permission.
