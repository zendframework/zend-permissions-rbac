# Methods

## `Zend\Permissions\Rbac\Role`

The `Role` provides the base functionality required by the `RoleInterface`.

Method signature                                | Description
----------------------------------------------- | -----------
`__construct(string $name) : void`              | Create a new instance with the provided name.
`getName() : string`                            | Retrieve the name assigned to this role.
`addPermission(string $name) : void`            | Add a permission for the current role.
`hasPermission(string $name) : bool`            | Does the role have the given permission?
`getPermissions(bool $children = true) : array` | Retrieve all permissions, including child permissions if `$children` is true.
`addChild(RoleInterface $child) : Role`         | Add a child role to the current instance.
`getChildren() : RoleInterface[]`               | Get all child roles.
`addParent(RoleInterface $parent) : Role`       | Add a parent role to the current instance.
`getParents() : RoleInterface[]`                | Get all parent roles.

## `Zend\Permissions\Rbac\AssertionInterface`

Custom assertions can be provided to `Rbac::isGranted()` (see below); such
assertions are provided the `Rbac` instance on invocation, along with the role
and permission being tested against.

Method signature                                                     | Description
-------------------------------------------------------------------- | -----------
`assert(Rbac $rbac, RoleInterface $role, string $permission) : bool` | Given an RBAC, a role, and a permission, determine if permission is granted.

## `Zend\Permissions\Rbac\Rbac`

`Rbac` is the object with which you will interact within your application in
order to query for permissions.

Method signature                                                              | Description
----------------------------------------------------------------------------- | -----------
`addRole(string\|RoleInterface $child, array\|RoleInterface $parents = null)` | Add a role to the RBAC. If `$parents` is non-null, the `$child` is also added to any parents provided.
`getRole(string $role) : RoleInterface`                                       | Get the role specified by name, raising an exception if not found.
`getRoles(): RoleInterface[]`                                                 | Retrieve all the roles.
`hasRole(string\|RoleInterface $role) : bool`                                 | Recursively queries the RBAC for the given role, returning `true` if found, `false` otherwise.
`getCreateMissingRoles() : bool`                                              | Retrieve the flag that determines whether or not `$parent` roles are added automatically if not present when calling `addRole()`.
`setCreateMissingRoles(bool $flag) : void`                                    | Set the flag that determines whether or not `$parent` roles are added automatically if not present when calling `addRole()`.
`isGranted(string\|RoleInterface $role, string $permission, $assert = null)`  | Determine if the role has the given permission. If `$assert` is provided and either an `AssertInterface` instance or callable, it will be queried before checking against the given role.
