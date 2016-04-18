<?php
/**
 * @link      http://github.com/phly/zend-servicemanager-interop for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/zend-servicemanager-interop/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\ServiceManager\Interop\TestAsset;

use Interop\Container\ServiceProvider;
use stdClass;

class CallableFactoryProvider implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'foo' => function ($container, $getPrevious = null) {
                $instance = new stdClass;
                $instance->callable = true;
                return $instance;
            },
        ];
    }
}
