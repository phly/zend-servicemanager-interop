# servicemanager-interop

[![Build Status](https://secure.travis-ci.org/phly/servicemanager-interop.svg?branch=master)](https://secure.travis-ci.org/phly/servicemanager-interop)
[![Coverage Status](https://coveralls.io/repos/github/phly/servicemanager-interop/badge.svg?branch=master)](https://coveralls.io/github/phly/servicemanager-interop?branch=master)

Use [container-interop service providers](https://github.com/container-interop/service-provider/)
with [zend-servicemanager](https://zendframework.github.io/zend-servicemanager/).

## Installation

```console
$ composer require phly/zend-servicemanager-interop
```

## Usage

```php
use Zend\ServiceManager\Interop\ConfigInjector;
use Zend\ServiceManager\Interop\ProviderAggregate;
use Zend\ServiceManager\ServiceManager;

// Get a list of service provider classes and aggregate them:
$aggregate = new ProviderAggregate();
foreach (include 'providers.php' as $provider) {
    $aggregate->enqueue($provider);
}

// Create and inject a service manager with the providers:
$container = (new ConfigInjector())->inject($aggregate, new ServiceManager());
```

## Internals

- `ProviderAggregate` allows passing either a class name of a provider, or an
  instance. Internally, it creates instances from class names to ensure that
  dequeued items are known-good types.
- `ConfigInjector` will create a closure around factories, to curry arguments
  and ensure the factories have no conflicts with how zend-servicemanager
  invokes them.
- `ConfigInjector` adds factories as delegators if the service is already
  present in the zend-servicemanager instance. Again, the factory is wrapped in
  a closure in order to curry arguments in the correct order.

## Differences from service-provider

This implementation experiments a bit and allows the following as factory
arguments:

- any valid PHP callable
- FQCN arguments that resolve to functors
