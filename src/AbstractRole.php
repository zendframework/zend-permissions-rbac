<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Permissions\Rbac;

use RecursiveIteratorIterator;

abstract class AbstractRole extends AbstractIterator implements RoleInterface
{
    /**
     * @var null|array
     */
    protected $parents;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $permissions = [];

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

        $it = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $leaf) {
            /** @var RoleInterface $leaf */
            if ($leaf->hasPermission($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a child.
     *
     * @param  RoleInterface|string $child
     * @return Role
     */
    public function addChild($child)
    {
        if (is_string($child)) {
            $child = new Role($child);
        }
        if (! $child instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Child must be a string or implement Zend\Permissions\Rbac\RoleInterface'
            );
        }

        $child->setParent($this);
        $this->children[] = $child;

        return $this;
    }

    /**
     * @param  RoleInterface $parent
     * @return RoleInterface
     */
    public function setParent($parent)
    {
        if (null === $this->parents) {
            $this->parents = [];
        }
        if (!in_array($parent, $this->parents)) {
            $this->parents[] = $parent;
        }
        return $this;
    }

    /**
     * @return null|RoleInterface|array
     */
    public function getParent()
    {
        if (1 === count($this->parents)) {
            return $this->parents[0];
        }
        return $this->parents;
    }

    /**
     * @param  RoleInterface $parent
     * @return RoleInterface
     */
    public function addParent($parent)
    {
        $parent->addChild($this);
        return $this->setParent($parent);
    }
}
