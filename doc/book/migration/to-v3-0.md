# Upgrading to 3.0

If you upgrade from version 2 releases, you will notice a few changes. This
document details the changes

## Minimum supported PHP version

Version 3 drops support for PHP versions prior to PHP 7.1.

## AssertionInterface

The primary change is the `Zend\Permissions\Rbac\AssertionInterface::assert()`
method definition.

The new `assert` method has the following signature:

```php
namespace Zend\Permissions\Rbac;

public function assert(
    Rbac $rbac,
    RoleInterface $role,
    string $permission
) : bool
```

The version 2 releases defined the method such that it only accepted a single
parameter, `Rbac $rbac`. Version 3 adds the `$role` and `$permission`
parameters. This simplifies implementation of dynamic assertions using the role
and the permission information.

For instance, imagine you want to disable a specific permission `foo` for an
`admin` role; you can implement that as follows:

```php
public function assert(Rbac $rbac, RoleInterface $role, string $permission) : bool
{
    return ! ($permission === 'foo' && $role->getName() === 'admin');
}
```

If you were previously implementing `AssertionInterface`, you will need to
update the `assert()` signature to match the changes in version 3.

If you were creating assertions as PHP callables, you may continue to use the
existing signature; however, you may also expand them to accept the new
arguments should they assist you in creating more complex, dynamic assertions.

## RoleInterface

`Zend\Permissions\Rbac\RoleInterface` also received a number of changes,
including type hints and method name changes.

### Type hints

With the update to [PHP 7.1](#minimum-supported-php-version), we also updated
the `RoleInterface` to provide:

- scalar type hints where applicable (`addPermission()` and `hasPermission()`).
- add return type hints (including scalar type hints) to all methods.

You will need to examine the `RoleInterface` definitions to determine what
changes to make to your implementations.

### setParent becomes addParent

In version 3, we renamed the method `Role::setParent()` to `Role::addParent()`.
This naming is more consistent with other method names, such as
`Role::addChild()`, and also makes clear that more than one parent may be
provided to any given role.

### getParent becomes getParents

In line with the previous change, `getParent()` was also renamed to
`getParents()`, which returns an array of `RoleInterface` instances.

### Removed support for string arguments in Role::addChild

Version 3 no longer allows adding a child using a string role name; you may only
provide `RoleInterface` instances.

### Adds getChildren

Since roles may have multiple children, the method `getChildren()` was added; it
returns an array of `RoleInterface` instances.
