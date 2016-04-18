<?php
/**
 * @link      http://github.com/phly/zend-servicemanager-interop for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/zend-servicemanager-interop/blob/master/LICENSE.md New BSD License
 */

namespace Zend\ServiceManager\Interop;

use InvalidArgumentException;
use Interop\Container\ServiceProvider;
use SplQueue;

/**
 * Aggregate ServiceProviders as a queue.
 */
class ProviderAggregate extends SplQueue
{
    /**
     * Append a value to the queue.
     *
     * @param string|ServiceProvider $value
     * @return void
     * @throws InvalidArgumentException for invalid $value types.
     */
    public function enqueue($value)
    {
        $value = $this->validate($value);
        parent::enqueue($value);
    }

    /**
     * Set a value at a specific index in the queue.
     *
     * @param mixed $index
     * @param string|ServiceProvider $value
     * @return void
     * @throws InvalidArgumentException for invalid $value types.
     */
    public function add($index, $value)
    {
        $value = $this->validate($value);
        parent::add($index, $value);
    }

    /**
     * Set a value at a specific index in the queue.
     *
     * @param mixed $index
     * @param string|ServiceProvider $value
     * @return void
     * @throws InvalidArgumentException for invalid $value types.
     */
    public function offsetSet($index, $value)
    {
        $value = $this->validate($value);
        parent::offsetSet($index, $value);
    }

    /**
     * Append a value to the queue.
     *
     * @param string|ServiceProvider $value
     * @return void
     * @throws InvalidArgumentException for invalid $value types.
     */
    public function push($value)
    {
        $value = $this->validate($value);
        parent::push($value);
    }

    /**
     * Prepend a value to the queue.
     *
     * @param string|ServiceProvider $value
     * @return void
     * @throws InvalidArgumentException for invalid $value types.
     */
    public function unshift($value)
    {
        $value = $this->validate($value);
        parent::unshift($value);
    }

    /**
     * Validate that a value is a ServiceProvider.
     *
     * @param mixed $value
     * @return ServiceProvider
     * @throws InvalidArgumentException if $value is not an instance of
     *     ServiceProvider, or not a class implementing the interface.
     */
    private function validate($value)
    {
        if ($value instanceof ServiceProvider) {
            return $value;
        }

        if (is_string($value) && $this->classImplementsServiceProvider($value)) {
            return new $value();
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid service provider (type "%s"); must be an object or class implementing %s',
            ( is_object($value) ? get_class($value) : gettype($value) ),
            ServiceProvider::class
        ));
    }

    /**
     * Does the given class implement ServiceProvider?
     *
     * @param string $class
     * @return bool
     */
    private function classImplementsServiceProvider($class)
    {
        if (! class_exists($class)) {
            return false;
        }

        $implements = class_implements($class);
        if (false === $implements) {
            return false;
        }

        return in_array(ServiceProvider::class, $implements);
    }
}
