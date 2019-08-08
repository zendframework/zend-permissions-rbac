<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Permissions\Rbac;

use ZendTest\Permissions\Rbac\RoleTest;

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
            return $this->roleSearchIncludingChildren($this->roles, $role);
        }

        return $this->roleSearchIncludingChildren($this->roles, $role->getName());
    }

    /**
     * @param $obj|Array
     * @param $needle|String
     * @return bool
     */
    private function roleSearchIncludingChildren($obj, $needle) : bool
    {
        $rv = 0;

        if (is_array($obj)) {
            foreach ($obj as $role) {
                $roleName = $role->getName();
                if ($roleName === $needle) {
                    $rv++;
                }

                $rv += $this->roleSearchIncludingChildren($role, $needle);
            }
        } else {
            $children = $obj->getChildren();

            // need to make sure the children are arrays (meaning they are added correctly)
            if (! is_array($children)) {
                return $rv ? true : false;
            }
            if (! count($children)) {
                return $rv ? true : false;
            } else {
                foreach ($children as $child) {
                    $roleName = $child->getName();
                    if ($roleName === $needle) {
                        $rv++;
                    }

                    $rv += $this->roleSearchIncludingChildren($child, $needle);
                }
            }
        }

        return $rv ? true : false;
    }

    /**
     * Get a registered role by name
     *
     * @throws Exception\InvalidArgumentException if role is not found.
     */
    public function getRole(string $needle) : RoleInterface
    {
        // tricky thing here is that $this->roles are an array of RoleInterface objects
        foreach ($this->roles as $role) {
            if ($role->getName() == $needle) {
                return $role;
            } else {
                $role = $this->getRoleSearchingChildren($role, $needle);
                if ($role != null) {
                    return $role;
                }
            }
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'No role with name "%s" could be found',
            $needle
        ));
    }

    /**
     * @param $obj RoleInterface
     * @param $needle String
     * @return null|RoleInterface
     */
    private function getRoleSearchingChildren($obj, $needle)
    {
        if (($obj instanceof RoleInterface) && ($obj->getName() == $needle)) {
            return $obj;
        } else {
            $children = $obj->getChildren();
            if (is_array($children) && ($children != null)) {
                $result = '';
                foreach ($children as $child) {
                    $result = $this->getRoleSearchingChildren($child, $needle);
                }
                return $result;
            }
        }
        return null;
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
