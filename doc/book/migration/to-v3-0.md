# Upgrading to 3.0

If you upgrade from v2.X you will notice a few changes. The main change is the
`Zend\Permissiones\Rbac\AssertionInterface::assert` function definition.

## AssertionInterface

The new `assert` functions looks as follows:

```php
public function assert(Rbac $rbac, RoleInterface $role, string $permission): bool;
```

In v2.X we had only the first parameter $rbac. In V3.0 we added `$role` and
`$permission` parameters. This will simplify the implementation of dynamic
assertion, using the Role and the permission information. For instance, imagine
you want to disable a specific permission `foo` for an `admin` role, you can
implement a simple function as follows:

```
public function assert(Rbac $rbac, RoleInterface $role, string $permission): bool
{
    return !($permission === 'foo' && $role->getName() === 'admin');
}
```

## Removed Role::setParent()

In v3.0 we removed the function `Role::setParent()` in favor of `Role::addParent()`.
This function is more consistent with the others function naming like
`Role::addChild()`.

##  Removed the support of string in Role::addChild()

In v3.0 you cannot add a child using a role name as string. You can only add
a `RoleInterface` object.
