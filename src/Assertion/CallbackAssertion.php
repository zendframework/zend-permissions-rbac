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
    protected $callback;

    /**
     * @param callable $callback The assertion callback
     */
    public function __construct($callback)
    {
        if (! is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback provided; not callable');
        }
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function assert(Rbac $rbac, RoleInterface $role = null, string $permission = null) : bool
    {
        return (bool) ($this->callback)($rbac, $role, $permission);
    }
}
