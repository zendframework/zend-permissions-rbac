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

    public function setCreateMissingRoles(bool $createMissingRoles) : void
    {
        $this->createMissingRoles = $createMissingRoles;
    }

    public function getCreateMissingRoles() : bool
    {
        return $this->createMissingRoles;
    }

    /**
     * Add a child.
     *
     * @param  string|RoleInterface $role
     * @param  null|array|RoleInterface $parents
     * @throws Exception\InvalidArgumentException if $role is not a string or
     *     RoleInterface.
     */
    public function addRole($role, $parents = null) : void
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
            $parents = is_array($parents) ? $parents : [$parents];
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
     */
    public function hasRole($role) : bool
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
        return isset($this->roles[$roleName])
            && $this->roles[$roleName] === $role;
    }

    /**
     * Get a registered role by name
     *
     * @throws Exception\InvalidArgumentException if role is not found.
     */
    public function getRole(string $roleName) : RoleInterface
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
     * Return all the roles
     *
     * @return RoleInterface[]
     */
    public function getRoles(): array
    {
        return array_values($this->roles);
    }

    /**
     * Determines if access is granted by checking the role and child roles for permission.
     *
     * @param RoleInterface|string $role
     * @param null|AssertionInterface|Callable $assertion
     * @throws Exception\InvalidArgumentException if the role is not found.
     * @throws Exception\InvalidArgumentException if the assertion is an invalid type.
     */
    public function isGranted($role, string $permission, $assertion = null) : bool
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
        if (false === $result || null === $assertion) {
            return $result;
        }

        if (! $assertion instanceof AssertionInterface
            && ! is_callable($assertion)
        ) {
            throw new Exception\InvalidArgumentException(
                'Assertions must be a Callable or an instance of Zend\Permissions\Rbac\AssertionInterface'
            );
        }

        if ($assertion instanceof AssertionInterface) {
            return $result && $assertion->assert($this, $role, $permission);
        }

        // Callable assertion provided.
        return $result && $assertion($this, $role, $permission);
    }
}
