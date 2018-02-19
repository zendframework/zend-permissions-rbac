<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Permissions\Rbac;

class Role implements RoleInterface
{
    /**
     * @var RoleInterface[]
     */
    protected $children = [];

    /**
     * @var RoleInterface[]
     */
    protected $parents = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $permissions = [];

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name of the role.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Add permission to the role.
     *
     * @param $name
     * @return void
     */
    public function addPermission(string $name) : void
    {
        $this->permissions[$name] = true;
    }

    /**
     * Checks if a permission exists for this role or any child roles.
     *
     * @param  string $name
     * @return bool
     */
    public function hasPermission(string $name) : bool
    {
        if (isset($this->permissions[$name])) {
            return true;
        }
        foreach ($this->children as $child) {
            if ($child->hasPermission($name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a child
     *
     * @param  RoleInterface $child
     * @return void
     * @throws Exception\RuntimeException
     */
    public function addChild(RoleInterface $child) : void
    {
        $childName = $child->getName();
        if ($this->hasAncestor($child)) {
            throw new Exception\CircularReferenceException(sprintf(
                "To prevent circular references, you cannot add role '%s' as child",
                $childName
            ));
        }
        if (! isset($this->children[$childName])) {
            $this->children[$childName] = $child;
            $child->addParent($this);
        }
    }

    /**
     * Check if a role is an ancestor
     *
     * @param RoleInterface $role
     * @return bool
     */
    protected function hasAncestor(RoleInterface $role) : bool
    {
        if (isset($this->parents[$role->getName()])) {
            return true;
        }
        foreach ($this->parents as $parent) {
            if ($parent->hasAncestor($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the children roles
     *
     * @return RoleInterface[]
     */
    public function getChildren() : array
    {
        return array_values($this->children);
    }

    /**
     * Add a parent role
     *
     * @param  RoleInterface $parent
     * @return void
     * @throws Exception\RuntimeException
     */
    public function addParent(RoleInterface $parent) : void
    {
        $parentName = $parent->getName();
        if ($this->hasDescendant($parent)) {
            throw new Exception\CircularReferenceException(sprintf(
                "To prevent circular references, you cannot add role '%s' as parent",
                $parentName
            ));
        }
        if (! isset($this->parents[$parentName])) {
            $this->parents[$parentName] = $parent;
            $parent->addChild($this);
        }
    }

    /**
     * Check if a role is a descendant
     *
     * @param RoleInterface $role
     * @return bool
     */
    protected function hasDescendant(RoleInterface $role) : bool
    {
        if (isset($this->children[$role->getName()])) {
            return true;
        }
        foreach ($this->children as $child) {
            if ($child->hasDescendant($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the parent roles
     *
     * @return RoleInterface[]
     */
    public function getParents() : array
    {
        return array_values($this->parents);
    }
}
