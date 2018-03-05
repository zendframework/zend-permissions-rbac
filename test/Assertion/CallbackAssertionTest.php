<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Permissions\Rbac\Assertion;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zend\Permissions\Rbac;

class CallbackAssertionTest extends TestCase
{
    /**
     * Ensures callback is set in object
     */
    public function testCallbackIsDecoratedAsClosure()
    {
        $callback = function () {
        };
        $assert = new Rbac\Assertion\CallbackAssertion($callback);
        $this->assertAttributeNotSame($callback, 'callback', $assert);
        $this->assertAttributeInstanceOf(Closure::class, 'callback', $assert);
    }

    /**
     * Ensures assert method provides callback with rbac as argument
     */
    public function testAssertMethodPassRbacToCallback()
    {
        $rbac = new Rbac\Rbac();
        $assert = new Rbac\Assertion\CallbackAssertion(function ($rbacArg) use ($rbac) {
            Assert::assertSame($rbacArg, $rbac);
            return false;
        });
        $foo = new Rbac\Role('foo');
        $foo->addPermission('can.foo');
        $rbac->addRole($foo);
        $this->assertFalse($rbac->isGranted($foo, 'can.foo', $assert));
    }

    /**
     * Ensures assert method returns callback's function value
     */
    public function testAssertMethod()
    {
        $rbac = new Rbac\Rbac();
        $foo  = new Rbac\Role('foo');
        $bar  = new Rbac\Role('bar');

        $assertRoleMatch = function ($role) {
            return function ($rbac) use ($role) {
                return $role->getName() === 'foo';
            };
        };

        $roleNoMatch = new Rbac\Assertion\CallbackAssertion($assertRoleMatch($bar));
        $roleMatch   = new Rbac\Assertion\CallbackAssertion($assertRoleMatch($foo));

        $foo->addPermission('can.foo');
        $bar->addPermission('can.bar');

        $rbac->addRole($foo);
        $rbac->addRole($bar);

        $this->assertFalse($rbac->isGranted($bar, 'can.bar', $roleNoMatch));
        $this->assertFalse($rbac->isGranted($bar, 'can.foo', $roleNoMatch));
        $this->assertTrue($rbac->isGranted($foo, 'can.foo', $roleMatch));
    }

    public function testAssertWithCallable()
    {
        $rbac = new Rbac\Rbac();
        $foo  = new Rbac\Role('foo');
        $foo->addPermission('can.foo');
        $rbac->addRole($foo);

        $callable = function ($rbac, $permission, $role) {
            return true;
        };
        $this->assertTrue($rbac->isGranted('foo', 'can.foo', $callable));
        $this->assertFalse($rbac->isGranted('foo', 'can.bar', $callable));
    }

    public function testAssertWithInvalidValue()
    {
        $rbac = new Rbac\Rbac();
        $foo  = new Rbac\Role('foo');
        $foo->addPermission('can.foo');
        $rbac->addRole($foo);

        $callable = new stdClass();
        $this->expectException(Rbac\Exception\InvalidArgumentException::class);
        $rbac->isGranted('foo', 'can.foo', $callable);
    }
}
