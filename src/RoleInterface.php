<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Permissions\Rbac;

interface RoleInterface
{
    /**
     * Get the name of the role.
     */
    public function getName() : string;

    /**
     * Add permission to the role.
     */
    public function addPermission(string $name) : void;

    /**
     * Checks if a permission exists for this role or any child roles.
     */
    public function hasPermission(string $name) : bool;

    /**
     * Add a child.
     */
    public function addChild(RoleInterface $child) : void;

    /**
     * Get the children roles.
     *
     * @return RoleInterface[]
     */
    public function getChildren() : iterable;

    /**
     * Add a parent.
     */
    public function addParent(RoleInterface $parent) : void;

    /**
     * Get the parent roles.
     *
     * @return RoleInterface[]
     */
    public function getParents() : iterable;
}
