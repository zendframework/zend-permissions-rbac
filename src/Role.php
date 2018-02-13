<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Permissions\Rbac;

class Role implements RoleInterface
{
    /**
     * @var RoleInterface[]
     */
    protected $childrens = [];

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add permission to the role.
     *
     * @param $name
     * @return RoleInterface
     */
    public function addPermission($name)
    {
        if (! is_string($name)) {
            throw new Exception\InvalidArgumentException(
                'The permission name must be a string'
            );
        }
        $this->permissions[$name] = true;
        return $this;
    }

    /**
     * Checks if a permission exists for this role or any child roles.
     *
     * @param  string $name
     * @return bool
     */
    public function hasPermission($name)
    {
        if (isset($this->permissions[$name])) {
            return true;
        }
        foreach ($this->childrens as $child) {
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
     * @return RoleInterface
     */
    public function addChild(RoleInterface $child)
    {
        if (! $child instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Child must implement Zend\Permissions\Rbac\RoleInterface'
            );
        }
        $childName = $child->getName();
        if ($this->hasAncestor($child)) {
            throw new Exception\RuntimeException(sprintf(
                "To prevent circular references, you cannot add role '%s' as child",
                $childName
            ));
        }
        if (! isset($this->childrens[$childName])) {
            $this->childrens[$childName] = $child;
            $child->addParent($this);
        }
        return $this;
    }

    /**
     * Check if a role is an ancestor
     *
     * @param RoleInterface $role
     * @return bool
     */
    protected function hasAncestor(RoleInterface $role)
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
    public function getChildrens()
    {
        return array_values($this->childrens);
    }

    /**
     * Add a parent role
     *
     * @param  RoleInterface $parent
     * @return RoleInterface
     */
    public function addParent(RoleInterface $parent)
    {
        if (! $parent instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Parent must implement Zend\Permissions\Rbac\RoleInterface'
            );
        }
        $parentName = $parent->getName();
        if ($this->hasDescendant($parent)) {
            throw new Exception\RuntimeException(sprintf(
                "To prevent circular references, you cannot add role '%s' as parent",
                $parentName
            ));
        }
        if (! isset($this->parents[$parentName])) {
            $this->parents[$parentName] = $parent;
            $parent->addChild($this);
        }
        return $this;
    }

    /**
     * Check if a role is a descendant
     *
     * @param RoleInterface $role
     * @return bool
     */
    protected function hasDescendant(RoleInterface $role)
    {
        if (isset($this->childrens[$role->getName()])) {
            return true;
        }
        foreach ($this->childrens as $child) {
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
    public function getParents()
    {
        return array_values($this->parents);
    }
}
