<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Permissions\Rbac;

interface RoleInterface
{
    /**
     * Get the name of the role.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Add permission to the role.
     *
     * @param $name
     * @return RoleInterface
     */
    public function addPermission(string $name): RoleInterface;

    /**
     * Checks if a permission exists for this role or any child roles.
     *
     * @param  string $name
     * @return bool
     */
    public function hasPermission(string $name): bool;

    /**
     * Add a child.
     *
     * @param  RoleInterface $child
     * @return RoleInterface
     */
    public function addChild(RoleInterface $child): RoleInterface;

    /**
     * Get the children roles.
     *
     * @return RoleInterface[]
     */
    public function getChildren(): array;

    /**
     * Add a parent.
     *
     * @param RoleInterface $parent
     * @return RoleInterface
     */
    public function addParent(RoleInterface $parent): RoleInterface;

    /**
     * Get the parent roles.
     *
     * @return RoleInterface[]
     */
    public function getParents(): array;
}
