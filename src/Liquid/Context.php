<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

/**
 * Context keeps the variable stack and resolves variables, as well as keywords.
 */
class Context
{
    /**
     * Local scopes
     *
     * @var array
     */
    protected $assigns;

    /**
     * Registers for non-variable state data
     *
     * @var array
     */
    public $registers;

    /**
     * The filterbank holds all the filters
     *
     * @var Filterbank
     */
    protected $filterbank;

    /**
     * Global scopes
     *
     * @var array
     */
    public $environments = array();

    /**
     * Constructor
     *
     * @param array $assigns
     * @param array $registers
     * @throws LiquidException
     * @throws \ReflectionException
     */
    public function __construct(array $assigns = array(), array $registers = array())
    {
        $this->assigns = array($assigns);
        $this->registers = $registers;
        $this->filterbank = new Filterbank($this);
        // first empty array serves as source for overrides, e.g. as in TagDecrement
        $this->environments = array(array(), $_SERVER);
    }


    /**
     * @return array
     */
    public function getAssigns()
    {
        foreach($this->assigns AS $a) {
            if($a) {
                return $a;
            }
        }
        return [];
    }

    /**
     * Add a filter to the context
     *
     * @param mixed $filter
     * @throws LiquidException
     * @throws \ReflectionException
     */
    public function addFilters($filter)
    {
        $this->filterbank->addFilter($filter);
    }

    /**
     * Invoke the filter that matches given name
     *
     * @param string $name The name of the filter
     * @param mixed $value The value to filter
     * @param array $args Additional arguments for the filter
     *
     * @return string
     */
    public function invoke($name, $value, array $args = array())
    {
        return $this->filterbank->invoke($name, $value, $args);
    }

    /**
     * Merges the given assigns into the current assigns
     *
     * @param array $newAssigns
     */
    public function merge($newAssigns)
    {
        $this->assigns[0] = array_merge($this->assigns[0], $newAssigns);
    }

    /**
     * Push new local scope on the stack.
     *
     * @return bool
     */
    public function push()
    {
        array_unshift($this->assigns, array());
        return true;
    }

    /**
     * Pops the current scope from the stack.
     *
     * @throws LiquidException
     */
    public function pop()
    {
        if (count($this->assigns) == 1) {
            throw new LiquidException('No elements to pop');
        }

        array_shift($this->assigns);
    }

    /**
     * Replaces []
     *
     * @param string
     *
     * @return mixed
     * @throws LiquidException
     */
    public function get($key)
    {
        return $this->resolve($key);
    }

    /**
     * Replaces []=
     *
     * @param string $key
     * @param mixed $value
     * @param bool $global
     */
    public function set($key, $value, $global = false)
    {
        if ($global) {
            for ($i = 0; $i < count($this->assigns); $i++) {
                $this->assigns[$i][$key] = $value;
            }
        } else {
            $this->assigns[0][$key] = $value;
        }
    }

    /**
     * Returns true if the given key will properly resolve
     *
     * @param string $key
     *
     * @return bool
     * @throws LiquidException
     */
    public function hasKey($key)
    {
        return (!is_null($this->resolve($key)));
    }

    /**
     * Resolve a key by either returning the appropriate literal or by looking up the appropriate variable
     *
     * Test for empty has been moved to interpret condition, in Decision
     *
     * @param string $key
     *
     * @throws LiquidException
     * @return mixed
     */
    private function resolve($key)
    {
        // This shouldn't happen
        if (is_array($key)) {
            throw new LiquidException("Cannot resolve arrays as key");
        }

        if (is_null($key) || $key == 'null') {
            return null;
        }

        if ($key == 'true') {
            return true;
        }

        if ($key == 'false') {
            return false;
        }

        if (preg_match('/^\'(.*)\'$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^"(.*)"$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(\d+)$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(\d[\d\.]+)$/', $key, $matches)) {
            return $matches[1];
        }

        return $this->variable($key);
    }

    /**
     * Fetches the current key in all the scopes
     *
     * @param string $key
     *
     * @return mixed
     */
    private function fetch($key)
    {
        // TagDecrement depends on environments being checked before assigns
        foreach ($this->environments as $environment) {
            if (array_key_exists($key, $environment)) {
                return $environment[$key];
            }
        }

        foreach ($this->assigns as $scope) {
            if (array_key_exists($key, $scope)) {
                $obj = $scope[$key];

                if ($obj instanceof Drop) {
                    $obj->setContext($this);
                }

                return $obj;
            }
        }

        return null;
    }

    /**
     * Resolved the namespaced queries gracefully.
     *
     * @param string $key
     *
     * @throws LiquidException
     * @return mixed
     */
    private function variable($key)
    {
        if (!preg_match_all("/(\[?[a-zA-Z0-9\s_-]+\]?)/", $key, $matches)) {
            return null;
        }

        $parts = array();
        foreach ($matches[1] as $match) {
            if (preg_match("/\[([a-zA-Z0-9\s_-]+)\]/i", $match, $m)) {
                array_push($parts, is_numeric($m[1]) ? $m[1] : $this->fetch($m[1]));
            } else {
                array_push($parts, $match);
            }
        }

        $object = $this->fetch(array_shift($parts));
        while (count($parts) > 0) {
            // since we still have a part to consider
            // and since we can't dig deeper into plain values
            // it can be thought as if it has a property with a null value
            if (!is_object($object) && !is_array($object)) {
                return null;
            }

            // first try to cast an object to an array or value
            if($object instanceof Model) {
            } elseif (method_exists($object, 'toLiquid')) {
                $object = $object->toLiquid();
            } elseif (method_exists($object, 'toArray')) {
                $object = $object->toArray();
            }

            if (is_null($object)) {
                return null;
            }

            if ($object instanceof Drop) {
                $object->setContext($this);
            }

            $nextPartName = array_shift($parts);

            if (is_array($object)) {
                // if the last part of the context variable is .size we just return the count
                if ($nextPartName == 'size' && count($parts) == 0 && !array_key_exists('size', $object)) {
                    return count($object);
                }

                // no key - no value
                if (!array_key_exists($nextPartName, $object)) {
                    return null;
                }

                $object = $object[$nextPartName];
                continue;
            }

            if (!is_object($object)) {
                // we got plain value, yet asked to resolve a part
                // think plain values have a null part with any name
                return null;
            }

            if ($object instanceof Drop) {
                // if the object is a drop, make sure it supports the given method
                if (!$object->hasKey($nextPartName)) {
                    return null;
                }

                $object = $object->invokeDrop($nextPartName);
                continue;
            }

            //
            if($object instanceof Model) {
                if(is_callable([$object, $nextPartName]) && method_exists($object, $nextPartName)) {
                    if($object->relationLoaded($nextPartName)) {
                        $object = $object->$nextPartName;
                    } else {
                        $value = call_user_func([$object, $nextPartName]);
                        if ($value instanceof Drop) {
                            $value->setContext($this);
                        }
                        $object = $value;
                    }
                    if($object instanceof Relation) {
                        $object = $object->get();
                    }
                    continue;
                } else {
                    $value =  $object->$nextPartName;
                    if ($value instanceof Drop) {
                        $value->setContext($this);
                        $object = $value;
                        continue;
                    }
                    return $value;
                }
            }

            // if it has `get` or `field_exists` methods
            if (method_exists($object, 'field_exists')) {
                if (!$object->field_exists($nextPartName)) {
                    return null;
                }

                $object = $object->get($nextPartName);
                continue;
            }

            // if it's just a regular object, attempt to access a public method
            if (is_callable(array($object, $nextPartName))) {
                $object = call_user_func(array($object, $nextPartName));
                continue;
            }

            // then try a property (independent of accessibility)
            if (property_exists($object, $nextPartName)) {
                $object = $object->$nextPartName;
                continue;
            }

            // we'll try casting this object in the next iteration
        }

        // finally, resolve an object to a string or a plain value. if collection return it
        if($object instanceof Collection) {
            return $object;
        } elseif (method_exists($object, '__toString')) {
            $object = (string)$object;
        } elseif (method_exists($object, 'toLiquid')) {
            $object = $object->toLiquid();
        }

        // if everything else fails, throw up
        if (is_object($object) && !($object instanceof \Traversable)) {
            throw new LiquidException(sprintf("Value of type %s has no `toLiquid` nor `__toString` methods", get_class($object)));
        }

        return $object;
    }
}
