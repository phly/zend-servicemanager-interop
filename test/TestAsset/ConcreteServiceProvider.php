<?php
/**
 * @link      http://github.com/phly/zend-servicemanager-interop for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/zend-servicemanager-interop/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\ServiceManager\Interop\TestAsset;

use Interop\Container\ServiceProvider;
use stdClass;

class ConcreteServiceProvider implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'foo' => 'createFoo',
            'bar' => 'createBar',
        ];
    }

    public static function createFoo()
    {
        return new stdClass();
    }

    public static function createBar()
    {
        return new stdClass();
    }
}
