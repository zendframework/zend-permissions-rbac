<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Permissions\Rbac;

class Rbac
{
    /**
     * @var RoleInterface[]
     */
    protected $roles = [];

    /**
     * flag: whether or not to create roles automatically if
     * they do not exist.
     *
     * @var bool
     */
    protected $createMissingRoles = false;

    /**
     * @param  bool $createMissingRoles
     * @return void
     */
    public function setCreateMissingRoles(bool $createMissingRoles): void
    {
        $this->createMissingRoles = $createMissingRoles;
    }

    /**
     * @return bool
     */
    public function getCreateMissingRoles(): bool
    {
        return $this->createMissingRoles;
    }

    /**
     * Add a child.
     *
     * @param  string|RoleInterface               $child
     * @param  array|RoleInterface|null           $parents
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function addRole($role, $parents = null): void
    {
        if (is_string($role)) {
            $role = new Role($role);
        }
        if (! $role instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Role must be a string or implement Zend\Permissions\Rbac\RoleInterface'
            );
        }

        if ($parents) {
            if (! is_array($parents)) {
                $parents = [$parents];
            }
            foreach ($parents as $parent) {
                if ($this->createMissingRoles && ! $this->hasRole($parent)) {
                    $this->addRole($parent);
                }
                if (is_string($parent)) {
                    $parent = $this->getRole($parent);
                }
                $parent->addChild($role);
            }
        }
        $this->roles[$role->getName()] = $role;
    }

    /**
     * Is a role registered?
     *
     * @param  RoleInterface|string $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        if (! is_string($role) && ! $role instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Role must be a string or implement Zend\Permissions\Rbac\RoleInterface'
            );
        }
        if (is_string($role)) {
            return isset($this->roles[$role]);
        }
        $roleName = $role->getName();
        return isset($this->roles[$roleName]) &&
               $this->roles[$roleName] === $role;
    }

    /**
     * Get a registered role by name
     *
     * @param  string $roleName
     * @return RoleInterface
     * @throws Exception\InvalidArgumentException
     */
    public function getRole(string $roleName): RoleInterface
    {
        if (! isset($this->roles[$roleName])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'No role with name "%s" could be found',
                $roleName
            ));
        }
        return $this->roles[$roleName];
    }

    /**
     * Determines if access is granted by checking the role and child roles for permission.
     *
     * @param  RoleInterface|string             $role
     * @param  string                           $permission
     * @param  AssertionInterface|Callable|null $assert
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isGranted($role, string $permission, $assert = null): bool
    {
        if (! $this->hasRole($role)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'No role with name "%s" could be found',
                is_object($role) ? $role->getName() : $role
            ));
        }
        if (is_string($role)) {
            $role = $this->getRole($role);
        }
        $result = $role->hasPermission($permission);
        if (false === $result || null === $assert) {
            return $result;
        }
        if ($assert instanceof AssertionInterface) {
            return $result && $assert->assert($this, $permission, $role);
        }
        if (is_callable($assert)) {
            return $result && $assert($this, $permission, $role);
        }
        throw new Exception\InvalidArgumentException(
            'Assertions must be a Callable or an instance of Zend\Permissions\Rbac\AssertionInterface'
        );
    }
}
