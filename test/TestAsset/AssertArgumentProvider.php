<?php
/**
 * @link      http://github.com/phly/zend-servicemanager-interop for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/zend-servicemanager-interop/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\ServiceManager\Interop\TestAsset;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use PHPUnit_Framework_Assert as Assertions;
use stdClass;

class AssertArgumentProvider implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'foo' => 'createFoo',
        ];
    }

    public static function createFoo(ContainerInterface $container, callable $getPrevious = null)
    {
        Assertions::assertTrue($container->has('foo'));
        Assertions::assertTrue((null === $getPrevious) || is_callable($getPrevious));
        return new stdClass;
    }
}
