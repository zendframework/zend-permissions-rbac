<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-rbac for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Permissions\Rbac;

use PHPUnit\Framework\TestCase;
use Zend\Permissions\Rbac\Exception;
use Zend\Permissions\Rbac\RoleInterface;
use Zend\Permissions\Rbac\Role;

class RoleTest extends TestCase
{
    public function testConstructor()
    {
        $foo = new Role('foo');
        $this->assertInstanceOf(RoleInterface::class, $foo);
    }

    public function testGetName()
    {
        $foo = new Role('foo');
        $this->assertEquals('foo', $foo->getName());
    }

    public function testAddPermission()
    {
        $foo = new Role('foo');
        $foo->addPermission('bar');
        $foo->addPermission('baz');

        $this->assertTrue($foo->hasPermission('bar'));
        $this->assertTrue($foo->hasPermission('baz'));
    }

    public function testInvalidPermission()
    {
        $perm = new \stdClass();
        $foo = new Role('foo');
        $this->expectException(\TypeError::class);
        $foo->addPermission($perm);
    }

    public function testAddChild()
    {
        $foo = new Role('foo');
        $bar = new Role('bar');
        $baz = new Role('baz');

        $foo->addChild($bar);
        $foo->addChild($baz);

        $this->assertEquals([$bar, $baz], $foo->getChildren());
    }

    public function testAddParent()
    {
        $foo = new Role('foo');
        $bar = new Role('bar');
        $baz = new Role('baz');

        $foo->addParent($bar);
        $foo->addParent($baz);
        $this->assertEquals([$bar, $baz], $foo->getParents());
    }

    public function testPermissionHierarchy()
    {
        $foo = new Role('foo');
        $foo->addPermission('foo.permission');

        $bar = new Role('bar');
        $bar->addPermission('bar.permission');

        $baz = new Role('baz');
        $baz->addPermission('baz.permission');

        // create hierarchy bar -> foo -> baz
        $foo->addParent($bar);
        $foo->addChild($baz);

        $this->assertTrue($bar->hasPermission('bar.permission'));
        $this->assertTrue($bar->hasPermission('foo.permission'));
        $this->assertTrue($bar->hasPermission('baz.permission'));

        $this->assertFalse($foo->hasPermission('bar.permission'));
        $this->assertTrue($foo->hasPermission('foo.permission'));
        $this->assertTrue($foo->hasPermission('baz.permission'));

        $this->assertFalse($baz->hasPermission('foo.permission'));
        $this->assertFalse($baz->hasPermission('bar.permission'));
        $this->assertTrue($baz->hasPermission('baz.permission'));
    }

    public function testCircleReferenceWithChild()
    {
        $foo = new Role('foo');
        $bar = new Role('bar');
        $baz = new Role('baz');
        $baz->addPermission('baz');

        $foo->addChild($bar);
        $bar->addChild($baz);
        $this->expectException(Exception\CircularReferenceException::class);
        $baz->addChild($foo);
    }

    public function testCircleReferenceWithParent()
    {
        $foo = new Role('foo');
        $bar = new Role('bar');
        $baz = new Role('baz');
        $baz->addPermission('baz');

        $foo->addParent($bar);
        $bar->addParent($baz);
        $this->expectException(Exception\CircularReferenceException::class);
        $baz->addParent($foo);
    }

    public function testGetPermissions()
    {
        $foo = new Role('foo');
        $foo->addPermission('foo.permission');
        $foo->addPermission('foo.2nd-permission');

        $bar = new Role('bar');
        $bar->addPermission('bar.permission');

        $baz = new Role('baz');
        $baz->addPermission('baz.permission');

        // create hierarchy bar -> foo -> baz
        $foo->addParent($bar);
        $foo->addChild($baz);

        $this->assertEquals([
            'bar.permission',
            'foo.permission',
            'foo.2nd-permission',
            'baz.permission'
        ], $bar->getPermissions());

        $this->assertEquals([
            'bar.permission'
        ], $bar->getPermissions(false));

        $this->assertEquals([
            'foo.permission',
            'foo.2nd-permission',
            'baz.permission'
        ], $foo->getPermissions());

        $this->assertEquals([
            'foo.permission',
            'foo.2nd-permission'
        ], $foo->getPermissions(false));

        $this->assertEquals([
            'baz.permission'
        ], $baz->getPermissions());

        $this->assertEquals([
            'baz.permission'
        ], $baz->getPermissions(false));
    }

    public function testAddTwoChildRole()
    {
        $foo = new Role('foo');
        $bar = new Role('bar');
        $baz = new Role('baz');

        $foo->addChild($bar);
        $foo->addChild($baz);

        $this->assertEquals([$foo], $bar->getParents());
        $this->assertEquals([$bar, $baz], $foo->getChildren());
    }

    public function testAddSameParent()
    {
        $foo = new Role('foo');
        $bar = new Role('bar');

        $foo->addParent($bar);
        $foo->addParent($bar);

        $this->assertEquals([$bar], $foo->getParents());
    }
}
