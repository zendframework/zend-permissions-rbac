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
use Zend\Permissions\Rbac;
use Zend\Permissions\Rbac\Exception;

class RbacTest extends TestCase
{
    /**
     * @var \Zend\Permissions\Rbac\Rbac
     */
    protected $rbac;

    public function setUp()
    {
        $this->rbac = new Rbac\Rbac();
    }

    public function testIsGrantedAssertion()
    {
        $foo = new Rbac\Role('foo');
        $bar = new Rbac\Role('bar');

        $true  = new TestAsset\SimpleTrueAssertion();
        $false = new TestAsset\SimpleFalseAssertion();

        $roleNoMatch = new TestAsset\RoleMustMatchAssertion($bar);
        $roleMatch   = new TestAsset\RoleMustMatchAssertion($foo);

        $foo->addPermission('can.foo');
        $bar->addPermission('can.bar');

        $this->rbac->addRole($foo);
        $this->rbac->addRole($bar);

        $this->assertEquals(true, $this->rbac->isGranted($foo, 'can.foo', $true));
        $this->assertEquals(false, $this->rbac->isGranted($bar, 'can.bar', $false));

        $this->assertEquals(false, $this->rbac->isGranted($foo, 'cannot', $true));
        $this->assertEquals(false, $this->rbac->isGranted($bar, 'cannot', $false));

        $this->assertEquals(false, $this->rbac->isGranted($bar, 'can.bar', $roleNoMatch));
        $this->assertEquals(false, $this->rbac->isGranted($bar, 'can.foo', $roleNoMatch));

        $this->assertEquals(true, $this->rbac->isGranted($foo, 'can.foo', $roleMatch));
    }

    public function testIsGrantedSingleRole()
    {
        $foo = new Rbac\Role('foo');
        $foo->addPermission('can.bar');

        $this->rbac->addRole($foo);

        $this->assertEquals(true, $this->rbac->isGranted('foo', 'can.bar'));
        $this->assertEquals(false, $this->rbac->isGranted('foo', 'can.baz'));
    }

    public function testIsGrantedChildRoles()
    {
        $foo = new Rbac\Role('foo');
        $bar = new Rbac\Role('bar');

        $foo->addPermission('can.foo');
        $bar->addPermission('can.bar');

        $this->rbac->addRole($foo);
        $this->rbac->addRole($bar, $foo);

        $this->assertEquals(true, $this->rbac->isGranted('foo', 'can.bar'));
        $this->assertEquals(true, $this->rbac->isGranted('foo', 'can.foo'));
        $this->assertEquals(true, $this->rbac->isGranted('bar', 'can.bar'));

        $this->assertEquals(false, $this->rbac->isGranted('foo', 'can.baz'));
        $this->assertEquals(false, $this->rbac->isGranted('bar', 'can.baz'));
    }

    public function testGetRole()
    {
        $foo = new Rbac\Role('foo');
        $this->rbac->addRole($foo);
        $this->assertEquals($foo, $this->rbac->getRole('foo'));
    }

    /**
     * @covers Zend\Permissions\Rbac\Rbac::hasRole()
     */
    public function testHasRole()
    {
        $foo = new Rbac\Role('foo');
        $snafu = new TestAsset\RoleTest('snafu');

        $this->rbac->addRole('bar');
        $this->rbac->addRole($foo);
        $this->rbac->addRole('snafu');

        // check that the container has the same object $foo
        $this->assertTrue($this->rbac->hasRole($foo));

        // check that the container has the same string "bar"
        $this->assertTrue($this->rbac->hasRole('bar'));

        // check that the container do not have the string "baz"
        $this->assertFalse($this->rbac->hasRole('baz'));

        // check that 'snafu' role and $snafu are different
        $this->assertNotEquals($this->rbac->getRole('snafu'), $snafu);
        $this->assertTrue($this->rbac->hasRole('snafu'));
        $this->assertFalse($this->rbac->hasRole($snafu));
    }

    public function testAddRoleFromString()
    {
        $this->rbac->addRole('foo');

        $foo = $this->rbac->getRole('foo');
        $this->assertInstanceOf('Zend\Permissions\Rbac\Role', $foo);
    }

    public function testAddRoleFromClass()
    {
        $foo = new Rbac\Role('foo');

        $this->rbac->addRole('foo');
        $foo2 = $this->rbac->getRole('foo');

        $this->assertEquals($foo, $foo2);
        $this->assertInstanceOf('Zend\Permissions\Rbac\Role', $foo2);
    }

    public function testAddRoleNotValid()
    {
        $foo = new \stdClass();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->rbac->addRole($foo);
    }

    public function testAddRoleWithParentsUsingRbac()
    {
        $foo = new Rbac\Role('foo');
        $bar = new Rbac\Role('bar');

        $this->rbac->addRole($foo);
        $this->rbac->addRole($bar, $foo);

        $this->assertEquals($bar->getParents(), [$foo]);
        $this->assertEquals([$bar], $foo->getChildrens());
    }


    public function testAddRoleWithAutomaticParentsUsingRbac()
    {
        $foo = new Rbac\Role('foo');
        $bar = new Rbac\Role('bar');

        $this->rbac->setCreateMissingRoles(true);
        $this->assertTrue($this->rbac->getCreateMissingRoles());
        $this->rbac->addRole($bar, $foo);

        $this->assertEquals($bar->getParents(), [$foo]);
        $this->assertEquals([$bar], $foo->getChildrens());
    }

    /**
     * @tesdox Test adding custom child roles works
     */
    public function testAddCustomChildRole()
    {
        $role = $this->getMockForAbstractClass(Rbac\RoleInterface::class);
        $this->rbac->setCreateMissingRoles(true)->addRole($role, ['parent']);

        $role->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('customchild'));

        $role->expects($this->once())
            ->method('hasPermission')
            ->with('test')
            ->will($this->returnValue(true));

        $this->assertTrue($this->rbac->isGranted('parent', 'test'));
    }

    public function testAddMultipleParentRole()
    {
        $adminRole = new Rbac\Role('Administrator');
        $adminRole->addPermission('user.manage');
        $this->rbac->addRole($adminRole);

        $managerRole = new Rbac\Role('Manager');
        $managerRole->addPermission('post.publish');
        $this->rbac->addRole($managerRole, ['Administrator']);

        $editorRole = new Rbac\Role('Editor');
        $editorRole->addPermission('post.edit');
        $this->rbac->addRole($editorRole);

        $viewerRole = new Rbac\Role('Viewer');
        $viewerRole->addPermission('post.view');
        $this->rbac->addRole($viewerRole, ['Editor', 'Manager']);

        $this->assertEquals('Viewer', $editorRole->getChildrens()[0]->getName());
        $this->assertEquals('Viewer', $managerRole->getChildrens()[0]->getName());
        $this->assertTrue($this->rbac->isGranted('Editor', 'post.view'));
        $this->assertTrue($this->rbac->isGranted('Manager', 'post.view'));

        $this->assertEquals($viewerRole->getParents(), [$editorRole, $managerRole]);
        $this->assertEquals($managerRole->getParents(), [$adminRole]);
        $this->assertEmpty($editorRole->getParents());
        $this->assertEmpty($adminRole->getParents());
    }

    public function testAddParentRole()
    {
        $adminRole = new Rbac\Role('Administrator');
        $adminRole->addPermission('user.manage');
        $this->rbac->addRole($adminRole);

        $managerRole = new Rbac\Role('Manager');
        $managerRole->addPermission('post.publish');
        $managerRole->addParent($adminRole);
        $this->rbac->addRole($managerRole);

        $editorRole = new Rbac\Role('Editor');
        $editorRole->addPermission('post.edit');
        $this->rbac->addRole($editorRole);

        $viewerRole = new Rbac\Role('Viewer');
        $viewerRole->addPermission('post.view');
        $viewerRole->addParent($editorRole);
        $viewerRole->addParent($managerRole);
        $this->rbac->addRole($viewerRole);

        // Check roles hierarchy
        $this->assertEquals([$viewerRole], $editorRole->getChildrens());
        $this->assertEquals([$viewerRole], $managerRole->getChildrens());
        $this->assertEquals($viewerRole->getParents(), [$editorRole, $managerRole]);
        $this->assertEquals($managerRole->getParents(), [$adminRole]);
        $this->assertEmpty($editorRole->getParents());
        $this->assertEmpty($adminRole->getParents());

        // Check permissions
        $this->assertTrue($this->rbac->isGranted('Editor', 'post.view'));
        $this->assertTrue($this->rbac->isGranted('Editor', 'post.edit'));
        $this->assertTrue($this->rbac->isGranted('Viewer', 'post.view'));
        $this->assertTrue($this->rbac->isGranted('Manager', 'post.view'));
        $this->assertTrue($this->rbac->isGranted('Administrator', 'post.view'));
        $this->assertTrue($this->rbac->isGranted('Administrator', 'post.publish'));
        $this->assertFalse($this->rbac->isGranted('Administrator', 'post.edit'));
        $this->assertFalse($this->rbac->isGranted('Manager', 'post.edit'));
        $this->assertFalse($this->rbac->isGranted('Viewer', 'post.edit'));
        $this->assertFalse($this->rbac->isGranted('Viewer', 'post.publish'));
        $this->assertFalse($this->rbac->isGranted('Viewer', 'user.manage'));
        $this->assertFalse($this->rbac->isGranted('Editor', 'user.manage'));
        $this->assertFalse($this->rbac->isGranted('Editor', 'post.publish'));
        $this->assertFalse($this->rbac->isGranted('Manager', 'user.manage'));
    }

    public function testAddTwoChildRole()
    {
        $foo = new Rbac\Role('foo');
        $bar = new Rbac\Role('bar');
        $baz = new Rbac\Role('baz');

        $foo->addChild($bar);
        $foo->addChild($baz);

        $this->assertEquals([$foo], $bar->getParents());
        $this->assertEquals([$bar, $baz], $foo->getChildrens());
    }

    public function testAddSameParent()
    {
        $foo = new Rbac\Role('foo');
        $bar = new Rbac\Role('bar');

        $foo->addParent($bar);
        $foo->addParent($bar);

        $this->assertEquals([$bar], $foo->getParents());
    }
}
