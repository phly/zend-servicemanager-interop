<?php
/**
 * @link      http://github.com/phly/zend-servicemanager-interop for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/zend-servicemanager-interop/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\ServiceManager\Interop;

use Interop\Container\ServiceProvider;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\Interop\ProviderAggregate;

class ProviderAggregateTest extends TestCase
{
    public function setUp()
    {
        $this->aggregate = new ProviderAggregate();
    }

    public function invalidProviders()
    {
        return [
            'null'                => null,
            'true'                => true,
            'false'               => false,
            'zero'                => 0,
            'int'                 => 1,
            '0-float'             => 0.0,
            'float'               => 1.1,
            'non-class-string'    => 'not-a-class',
            'non-provider-string' => __CLASS__,
            'array'               => ['invalid'],
            'non-provider-object' => (object) ['provider' => 'invalid'],
        ];
    }

    public function injectionMethodAndIndex()
    {
        return [
            'enqueue'   => [ 'enqueue', null ],
            'add'       => [ 'add', 0 ],
            'offsetSet' => [ 'offsetSet', 0 ],
            'push'      => [ 'push', null ],
            'unshift'   => [ 'unshift', null ],
        ];
    }

    public function injectInvalidProvidersByMethod()
    {
        foreach ($this->injectionMethodAndIndex() as $arguments) {
            list($method, $index) = $arguments;
            foreach ($this->invalidProviders() as $name => $type) {
                $testCase = sprintf('%s-%s', $method, $name);
                yield $testCase => [$method, $type, $index];
            }
        }
    }

    /**
     * @dataProvider injectInvalidProvidersByMethod
     */
    public function testRaisesExceptionForAttemptsToInjectInvalidType($method, $type, $index)
    {
        $args = ($index === null) ? [$type] : [$index, $type];
        $this->setExpectedException(InvalidArgumentException::class, 'Invalid service provider');
        call_user_func_array([$this->aggregate, $method], $args);
    }

    /**
     * @dataProvider injectionMethodAndIndex
     */
    public function testWillAggregateServiceProviderInstances($method, $index)
    {
        $provider = $this->prophesize(ServiceProvider::class)->reveal();

        if ($index === null) {
            // No index; test that the item is added to the queue
            $this->aggregate->$method($provider);
            $this->assertSame($provider, $this->aggregate->dequeue());
        } else {
            // Index provided; test that the item is added to the queue at the index.
            // This requires injecting another instance first to ensure no
            // OutOfBoundsExceptions are raised.
            $this->aggregate->enqueue(new TestAsset\ConcreteServiceProvider());
            $this->aggregate->$method($index, $provider);
            $this->assertSame($provider, $this->aggregate->offsetGet($index));
        }
    }

    /**
     * @dataProvider injectionMethodAndIndex
     */
    public function testMarshalsServiceProviderInstanceFromString($method, $index)
    {
        if ($index === null) {
            // No index; test that the item is added to the queue
            $this->aggregate->$method(TestAsset\ConcreteServiceProvider::class);
            $this->assertInstanceOf(TestAsset\ConcreteServiceProvider::class, $this->aggregate->dequeue());
        } else {
            // Index provided; test that the item is added to the queue at the index.
            // This requires injecting another instance first to ensure no
            // OutOfBoundsExceptions are raised.
            $this->aggregate->enqueue(new TestAsset\ConcreteServiceProvider());
            $this->aggregate->$method($index, TestAsset\ConcreteServiceProvider::class);
            $this->assertInstanceOf(TestAsset\ConcreteServiceProvider::class, $this->aggregate->offsetGet($index));
        }
    }
}
