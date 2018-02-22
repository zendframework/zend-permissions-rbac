<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Permissions\Rbac;

interface AssertionInterface
{
    /**
     * Assertion method - must return a boolean.
     */
    public function assert(Rbac $rbac, RoleInterface $role, string $permission) : bool;
}
