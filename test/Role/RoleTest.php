<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Permissions
 */

namespace ZendTest\Permissions\Rbac\Role;

use Zend\Permissions\Rbac\Role\Role;

/**
 * @covers \Rbac\Role\Role
 * @group Coverage
 */
class RoleTest extends \PHPUnit_Framework_TestCase
{
    public function testSetNameByConstructor()
    {
        $role = new Role('phpIsHell');
        $this->assertEquals('phpIsHell', $role->getName());
    }

    /**
     * @covers Rbac\Role\Role::addPermission
     */
    public function testRoleCanAddPermission()
    {
        $role = new Role('php');

        $role->addPermission('debug');
        $this->assertTrue($role->hasPermission('debug'));

        $role->addPermission('delete');

        $this->assertTrue($role->hasPermission('delete'));
    }

    /**
     * @covers Rbac\Role\Role::getPermissions
     */
    public function testRoleCanGetPermissions()
    {
        $role = new Role('php');

        $role->addPermission('foo');
        $role->addPermission('bar');

        $expectedPermissions = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];
        $this->assertEquals($expectedPermissions, $role->getPermissions());
    }
}
