<?php
/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Permissions\Rbac\Assertion;

use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\Exception\InvalidArgumentException;
use Zend\Permissions\Rbac\Rbac;

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
     * {@inheritDoc}
     */
    public function assert(Rbac $rbac, $permission = null, $role = null)
    {
        return (bool) call_user_func($this->callback, $rbac);
    }
}
