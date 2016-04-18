<?php
/**
 * @link      http://github.com/phly/zend-servicemanager-interop for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/zend-servicemanager-interop/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\ServiceManager\Interop;

use Interop\Container\ServiceProvider;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;
use stdClass;
use Zend\ServiceManager\Interop\ContainerInjector;
use Zend\ServiceManager\Interop\ProviderAggregate;
use Zend\ServiceManager\ServiceManager;

class ContainerInjectorTest extends TestCase
{
    public function setUp()
    {
        $this->aggregate = new ProviderAggregate();
        $this->injector  = new ContainerInjector();
        $this->container = new ServiceManager();
    }

    public function testInjectsFactoriesForEachDiscreteService()
    {
        $provider = new TestAsset\ConcreteServiceProvider();
        $this->aggregate->enqueue($provider);
        $container = $this->injector->inject($this->aggregate, $this->container);
        $this->assertSame($this->container, $container);

        foreach (array_keys($provider->getServices()) as $service) {
            $this->assertTrue($container->has($service));
        }
    }

    public function testAddsDelegatorsWhenServiceAddedMultipleTimes()
    {
        $this->aggregate->enqueue(TestAsset\ConcreteServiceProvider::class);
        $this->aggregate->enqueue(TestAsset\OverrideProvider::class);
        $container = $this->injector->inject($this->aggregate, $this->container);

        $instance = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $instance);
        $this->assertAttributeEquals('FOO', 'foo', $instance);
    }

    public function testDefinitionOrderMattersWhenOverriding()
    {
        $this->aggregate->enqueue(TestAsset\OverrideProvider::class);
        $this->aggregate->enqueue(TestAsset\ConcreteServiceProvider::class);
        $container = $this->injector->inject($this->aggregate, $this->container);

        $instance = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $instance);
        $this->assertFalse(isset($instance->foo));
    }

    public function testFactoryReceivesNullSecondArgumentFromServiceManager()
    {
        $this->aggregate->enqueue(TestAsset\AssertArgumentProvider::class);
        $container = $this->injector->inject($this->aggregate, $this->container);
        $container->get('foo');
    }

    public function invalidFactoryMethodTypes()
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'zero'   => [0],
            'int'    => [1],
            '0float' => [0.0],
            'float'  => [1.1],
            'array'  => [['getServices']],
            'object' => [(object)['factory' => 'getServices']],
        ];
    }

    /**
     * @dataProvider invalidFactoryMethodTypes
     */
    public function testInjectorRaisesExceptionForInvalidMethodArgumentType($factory)
    {
        TestAsset\InvalidMethodTypeProvider::$factory = $factory;
        $this->aggregate->enqueue(TestAsset\InvalidMethodTypeProvider::class);
        $this->setExpectedException(RuntimeException::class, '(not a method or factory name)');
        $container = $this->injector->inject($this->aggregate, $this->container);
    }

    public function testInjectorRaisesExceptionForNonInvokableFactoryClass()
    {
        $this->aggregate->enqueue(TestAsset\UninvokableFactoryProvider::class);
        $this->setExpectedException(RuntimeException::class, '(factory is not invokable)');
        $container = $this->injector->inject($this->aggregate, $this->container);
    }

    public function testInjectorRaisesExceptionForMissingMethod()
    {
        $this->aggregate->enqueue(TestAsset\MissingMethodProvider::class);
        $this->setExpectedException(RuntimeException::class, '(method does not exist)');
        $container = $this->injector->inject($this->aggregate, $this->container);
    }

    public function testAllowsCallableFactories()
    {
        $this->aggregate->enqueue(TestAsset\CallableFactoryProvider::class);
        $container = $this->injector->inject($this->aggregate, $this->container);
        $this->assertTrue($container->has('foo'));
        $instance = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $instance);
        $this->assertAttributeSame(true, 'callable', $instance);
    }

    public function testAllowsInvokableFactories()
    {
        $this->aggregate->enqueue(TestAsset\InvokableFactoryProvider::class);
        $container = $this->injector->inject($this->aggregate, $this->container);
        $this->assertTrue($container->has('foo'));
        $instance = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $instance);
        $this->assertAttributeSame(true, 'invokable', $instance);
    }
}
