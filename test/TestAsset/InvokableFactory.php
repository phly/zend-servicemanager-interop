<?php
/**
 * @link      http://github.com/phly/zend-servicemanager-interop for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/zend-servicemanager-interop/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\ServiceManager\Interop\TestAsset;

use stdClass;

class InvokableFactory
{
    public function __invoke($container, $getPrevious = null)
    {
        $instance = new stdClass;
        $instance->invokable = true;
        return $instance;
    }
}
