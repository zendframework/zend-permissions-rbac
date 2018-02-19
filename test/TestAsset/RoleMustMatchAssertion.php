<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Permissions\Rbac\TestAsset;

use Zend\Permissions\Rbac\AbstractRole;
use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\RoleInterface;

class RoleMustMatchAssertion implements AssertionInterface
{
    public function assert(Rbac $rbac, RoleInterface $role = null, string $permission = null) : bool
    {
        return $role->getName() === 'foo';
    }
}
