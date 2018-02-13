<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

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
        $this->assertInstanceOf(RoleInterface::class, $foo->addPermission('bar'));
        $this->assertInstanceOf(RoleInterface::class, $foo->addPermission('baz'));
        $this->assertTrue($foo->hasPermission('bar'));
        $this->assertTrue($foo->hasPermission('baz'));
    }

    public function testInvalidPermission()
    {
        $perm = new \stdClass();
        $foo = new Role('foo');
        $this->expectException(Exception\InvalidArgumentException::class);
        $foo->addPermission($perm);
    }

    public function testAddChild()
    {
        $foo = new Role('foo');
        $bar = new Role('bar');
        $baz = new Role('baz');

        $this->assertInstanceOf(RoleInterface::class, $foo->addChild($bar));
        $this->assertInstanceOf(RoleInterface::class, $foo->addChild($baz));
        $this->assertEquals($foo->getChildrens(), [$bar, $baz]);
    }

    public function testAddParent()
    {
        $foo = new Role('foo');
        $bar = new Role('bar');
        $baz = new Role('baz');

        $this->assertInstanceOf(RoleInterface::class, $foo->addParent($bar));
        $this->assertInstanceOf(RoleInterface::class, $foo->addParent($baz));
        $this->assertEquals($foo->getParents(), [$bar, $baz]);
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
        $this->expectException(Exception\RuntimeException::class);
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
        $this->expectException(Exception\RuntimeException::class);
        $baz->addParent($foo);
    }
}
