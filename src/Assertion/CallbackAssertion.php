<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Permissions\Rbac\Assertion;

use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\Exception\InvalidArgumentException;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\RoleInterface;

class CallbackAssertion implements AssertionInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        // Cast callable to a closure to enforce type safety.
        $this->callback = function (
            Rbac $rbac,
            RoleInterface $role = null,
            string $permission = null
        ) use ($callback) : bool {
            return $callback($rbac, $role, $permission);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function assert(Rbac $rbac, RoleInterface $role, string $permission) : bool
    {
        return ($this->callback)($rbac, $role, $permission);
    }
}
