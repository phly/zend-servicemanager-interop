<?php
/**
 * @link      http://github.com/phly/zend-servicemanager-interop for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/zend-servicemanager-interop/blob/master/LICENSE.md New BSD License
 */

namespace Zend\ServiceManager\Interop;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use RuntimeException;
use Zend\ServiceManager\ServiceManager;

/**
 * Inject a ServiceManager instance with services defined in service providers.
 */
class ContainerInjector
{
    /**
     * Inject the ServiceManager based on the providers.
     *
     * Providers are traversed in the order in which they were added to the
     * aggregate, and the services of each used to seed the container.
     *
     * @param ProviderAggregate $providers
     * @param ServiceManager $container
     * @return ServiceManager
     * @throws RuntimeException if any factory returned by any provider is not
     *     callable.
     */
    public function inject(ProviderAggregate $providers, ServiceManager $container)
    {
        foreach ($providers as $provider) {
            $this->marshalServiceProvider($provider, $container);
        }

        return $container;
    }

    /**
     * Iterates through all services of a service provider, seeding the container.
     *
     * For each service it:
     *
     * - adds the specified factory, if the service does not already exist.
     * - adds a delegator factory otherwise.
     *
     * @param ServiceProvider $provider
     * @param ServiceManager $container
     * @return ServiceManager
     * @throws RuntimeException if any factory listed is not callable.
     */
    private function marshalServiceProvider(ServiceProvider $provider, ServiceManager $container)
    {
        foreach ($provider->getServices() as $service => $factory) {
            $callable = is_callable($factory) ? $factory : $this->marshalCallable($provider, $factory, $service);

            if ($container->has($service)) {
                $container->addDelegator($service, $this->createDelegator($callable));
                continue;
            }

            $container->setFactory($service, $this->createFactory($callable));
        }

        return $container;
    }

    /**
     * Marshal a callable from a service-provider definition.
     *
     * Attempts to create a `[classname, methodname]` callable from the
     * provider class name and the method.
     *
     * If the result is not callable, raises an exception; otherwise, returns
     * the result.
     *
     * @param ServiceProvider $provider
     * @param string $method
     * @param string $service
     * @return callable
     * @throws RuntimeException if the provider::method combination is not callable.
     */
    private function marshalCallable(ServiceProvider $provider, $method, $service)
    {
        if (is_callable($method)) {
            return $method;
        }

        if (! is_string($method)) {
            throw new RuntimeException(sprintf(
                '%s defines an invalid factory for "%s": %s (not a method or factory name)',
                get_class($provider),
                $service,
                (is_object($method) ? get_class($method) : var_export($method, true))
            ));
        }

        if (class_exists($method)) {
            return $this->marshalInstanceFactory($method, $service, $provider);
        }

        if (! method_exists($provider, $method)) {
            throw new RuntimeException(sprintf(
                '%s defines an invalid factory for "%s": %s (method does not exist)',
                get_class($provider),
                $service,
                $method
            ));
        }

        return [get_class($provider), $method];
    }

    /**
     * Marshal a class-based, invokable factory.
     *
     * @param string $class
     * @param string $service
     * @param ServiceProvider $provider
     * @return object Instance of type $class
     * @throws RuntimeException if a $class instance is not invokable.
     */
    private function marshalInstanceFactory($class, $service, ServiceProvider $provider)
    {
        $factory = new $class();

        if (! is_callable($factory)) {
            throw new RuntimeException(sprintf(
                '%s defines an invalid factory for "%s": %s (factory is not invokable)',
                get_class($provider),
                $service,
                $class
            ));
        }

        return $factory;
    }

    /**
     * Create and return a factory compatible with zend-servicemanager.
     *
     * Creates a closure around the callable which returns the result of
     * invoking it with the container.
     *
     * @param callable $callable
     * @return callable
     */
    private function createFactory(callable $callable)
    {
        return function (ContainerInterface $container) use ($callable) {
            return $callable($container);
        };
    }

    /**
     * Create and return a delegator factory for a service.
     *
     * Returns a delegator factory closure binding to the provided $factory,
     * and returning the result of invoking it with the container and the
     * callback.
     *
     * @param callable $factory
     * @return callable
     */
    private function createDelegator(callable $factory)
    {
        return function ($container, $name, $callback) use ($factory) {
            return $factory($container, $callback);
        };
    }
}
