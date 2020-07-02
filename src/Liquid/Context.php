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

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Iterator;
use IteratorAggregate;
use Liquid\Contracts\DropContract;
use Liquid\Exceptions\FilterError;
use Liquid\Exceptions\SyntaxError;
use Liquid\Tokens\TagToken;
use Liquid\Tokens\TextToken;
use Liquid\Tokens\VariableToken;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Traversable;

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
     * Local scopes
     *
     * @var array
     */
    protected $assigns_globals = array();

    /**
     * Registers for non-variable state data
     *
     * @var array
     */
    public $registers;

    /**
     * Global scopes
     *
     * @var array
     */
    public $environments = array();

    /**
     * The registered filter objects
     *
     * @var array
     */
    private $filters = array();

    /**
     * A map of all filters and the class that contain them (in the case of methods)
     *
     * @var array
     */
    private $methodMap = array();
    /**
     * mark if push new level for assigns
     *
     * @var bool|integer
     */
    private $push = false;
    /**
     * @var TagToken|TextToken|VariableToken
     */
    private $token;

    public $_error;

    /**
     * List with magick methods to ignore for filters
     *
     * @var array
     */
    private $_disallow_magick_methods = [
        '__construct', '__destruct', '__call',
        '__callstatic', '__get', '__set', '__isset',
        '__unset', '__sleep', '__wakeup', '__tostring',
        '__invoke', '__set_state', '__clone', '__debuginfo',
    ];

    /**
     * Constructor
     *
     * @param array $assigns
     * @param array $registers
     */
    public function __construct(array $assigns = array(), array $registers = array())
    {
        $this->assigns = array($assigns);
        $this->registers = $registers;
        // first empty array serves as source for overrides, e.g. as in TagDecrement
        $this->environments = array(array(), $_SERVER);
    }

    /**
     * Add a filter to the context
     *
     * @param mixed $filter
     * @throws LiquidException
     * @throws ReflectionException
     */
    public function addFilter($filter)
    {

        // If the passed filter was an object, store the object for future reference.
        if (is_object($filter)) {
            $filter->context = $this;
            $name = get_class($filter);
            $this->filters[$name] = $filter;
            $filter = $name;
        }

        // If it wasn't an object an isn't a string either, it's a bad parameter
        if (!is_string($filter)) {
            throw new LiquidException("Parameter passed to addFilter must be an object or a string");
        }

        // If the filter is a class, register all its methods
        if (class_exists($filter)) {
            $reflection = new ReflectionClass($filter);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) AS $method) {
                if (($methodName = $method->getName()) && !in_array(strtolower($methodName), $this->_disallow_magick_methods)) {
                    $this->methodMap[$methodName] = $filter;
                }
            }

            return;
        }

        // If it's a function register it simply
        if (function_exists($filter)) {
            $this->methodMap[$filter] = false;
            return;
        }

        throw new LiquidException("Parameter passed to addFilter must a class or a function");
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
        array_unshift($args, $value);
        $this->_error = null;

        // Consult the mapping
        if (isset($this->methodMap[$name])) {
            $class = $this->methodMap[$name];

            // If we have a registered object for the class, use that instead
            if (isset($this->filters[$class])) {
                $class = $this->filters[$class];
            }

            try {
                $args = array_map(function($input) {
                    return $input instanceof Traversable ? iterator_to_array($input) : $input;
                }, $args);

                // If we're calling a function
                if ($class === false) {
                    return call_user_func_array($name, $args);
                } else {
                    return call_user_func_array([new $class($this), $name], $args);
                }
            } catch (FilterError $e) {
                return $this->_error = $e->getMessage();
            }
        }

        return $value;
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
        $this->push = 0;
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
        $this->push = false;
    }

    /**
     * Replaces []
     *
     * @param string
     *
     * @return mixed
     * @throws LiquidException
     * @throws SyntaxError
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
        if($global) {
            $this->assigns_globals[$key] = $value;
        } elseif($this->push !== false) {
            $this->assigns[$this->push][$key] = $value;
        } else {
            $this->assigns[0][$key] = $value;
        }
    }

    /**
     * Replaces []=
     *
     * @param string $key
     * @param mixed $value
     */
    public function put($key, $value)
    {
        $this->assigns[count($this->assigns) - 1][$key] = $value;
    }

    /**
     * Returns true if the given key will properly resolve
     *
     * @param string $key
     *
     * @return bool
     * @throws LiquidException
     * @throws SyntaxError
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
     * @return mixed
     * @throws LiquidException
     * @throws SyntaxError
     */
    private function resolve($key)
    {
        // This shouldn't happen
        if (is_array($key)) {
            throw new LiquidException("Cannot resolve arrays as key");
        }

        if(is_string($key)) {
            $key = trim($key);
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

        if (in_array($key, ['empty', 'blank'])) {
            return $key;
        }

        //array key argument
        if(substr($key, -1) === ':') {
            return substr($key, 0, -1);
        }

        if (preg_match('/^\'(.*)\'$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^"(.*)"$/', $key, $matches)) {
            return $matches[1];
        }

        if(is_numeric($key)) {
            if(($check = filter_var($key, FILTER_VALIDATE_INT)) !== false) {
                return $check;
            }

            return (float)$key;
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
        if($this->push !== false && array_key_exists($this->push, $this->assigns)) {
            foreach ([$this->assigns[$this->push]] as $scope) {
                if (array_key_exists($key, $scope)) {
                    $obj = $scope[$key];

                    if ($obj instanceof DropContract) {
                        $obj->setContext($this);
                    }

                    return $obj;
                }
            }
        }

        // TagDecrement depends on environments being checked before assigns
        foreach ($this->environments as $environment) {
            if (array_key_exists($key, $environment)) {
                return $environment[$key];
            }
        }

        if(array_key_exists($key, $this->assigns_globals)) {
            return $this->assigns_globals[$key];
        }

        foreach ($this->assigns as $scope) {
            if (array_key_exists($key, $scope)) {
                $obj = $scope[$key];

                if ($obj instanceof DropContract) {
                    $obj->setContext($this);
                }

                return $obj;
            }
        }

        return null;
    }

    /**
     * Transform IteratorAggregate to array
     *
     * @param mixed $object
     *
     * @return mixed
     * @throws Exception
     */
    private function transformIteratorAggregate($object)
    {
        if($object instanceof IteratorAggregate) {
            if(($iterator = $object->getIterator()) && method_exists($iterator, 'getArrayCopy')) {
                return $iterator->getArrayCopy();
            }

            $object = array();
        }

        return $object;
    }

    /**
     * Resolved the namespaced queries gracefully.
     *
     * @param string $key
     *
     * @return mixed
     * @throws LiquidException
     * @throws SyntaxError
     * @throws Exception
     */
    private function variable($key)
    {
        if (!preg_match_all("/(\[?[a-zA-Z0-9\s_-]+\]?)/", $key, $matches)) {
            return null;
        }

        $key = preg_replace_callback("/\[(([^\[\]]*|(?R))*)\]/", function($match) {
            if(preg_match('~^(["|\'])(.*)\\1$~', $match[1], $m)) {
                return sprintf('["%s"]', $m[2]);
            }
            return sprintf('["%s"]', $this->variable($match[1]) ? : $match[1]);
        }, $key);

        if(preg_match_all("~['\"][^'\"]++['\"]|[^.\"\'\[\]]++~", $key,$result)) {
            $parts = $result[0];
        } else {
            $parts = preg_split('/(\.|\[|\])/', $key, null, PREG_SPLIT_NO_EMPTY);
        }

        $parts = array_map(function($part) {
            if(preg_match('~^"(.*)"$~', $part, $m)) {
                return $m[1];
            }
            return $part;
        }, $parts);

        $object = $this->value($this->transformIteratorAggregate($this->fetch(array_shift($parts))));

        while (count($parts) > 0) {
            if ($object instanceof DropContract) {
                $object->setContext($this);
            }

            $nextPartName = array_shift($parts);

            if($nextPartName == 'empty?') {
                return empty($object);
            }

            if($nextPartName == 'size' && count($parts) == 0) {
                return $this->getSize($object);
            }

            $object = $this->getValue($object, $nextPartName);
        }

        // finally, resolve an object to a string or a plain value. if collection return it
        if (is_object($object) && method_exists($object, '__toString') && !($object instanceof DropContract)) {
            $object = (string)$object;
        }

        // if everything else fails, throw up
        if (
            is_object($object) &&
            !($object instanceof Traversable) &&
            !($object instanceof DropContract) &&
            !($object instanceof Model) &&
            !($object instanceof Builder) &&
            !($object instanceof Relation)
        ) {
            if($token = $this->getToken()) {
                throw new SyntaxError(sprintf("Value of type %s has no `__toString` methods", get_class($object)), $token);
            }

            throw new LiquidException(sprintf("Value of type %s has no `__toString` methods", get_class($object)));
        }

        return $object;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function value($value)
    {
        return $value instanceof Closure ? $value($this) : $value;
    }

    /**
     * @param $element
     * @param $property
     * @return bool
     */
    public function hasGetValue($element, $property)
    {
        if(
            is_callable($element) ||
            (is_array($element) && array_key_exists($property, $element)) ||
            ($element instanceof ArrayAccess && $element->offsetExists($property)) ||
            (is_object($element) && is_callable($element, $property)) ||
            (is_object($element) && property_exists($element, $property) && isset($element->$property)) ||
            ($element instanceof DropContract)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $element
     * @param string $property
     * @return mixed
     */
    public function getValue($element, $property)
    {
        if (is_callable($element)) {
            return $element($this);
        } elseif (is_array($element) && array_key_exists($property, $element)) {
            return $element[$property];
        } elseif($element instanceof ArrayAccess && $element->offsetExists($property)) {
            return $element->offsetGet($property);
        } elseif(is_object($element) && is_callable($element, $property)) {
            return $element->$property();
        } elseif(is_object($element) && property_exists($element, $property) && isset($element->$property)) {
            return $element->$property;
        } elseif($element instanceof DropContract) {
            if(!$element->hasKey($property)) {
                return null;
            }

            return $element->invokeDrop($property);
        }

        return null;
    }

    /**
     * @param mixed $element
     * @return mixed
     */
    public function getSize($element)
    {
        if(is_array($element)) {
            return count($element);
        } elseif($element instanceof Countable) {
            return $element->count();
        } elseif($element instanceof ArrayAccess) {
            $total = 0;
            foreach($element AS $e) {
                $total++;
            }
            return $total;
        } elseif ($element instanceof Iterator) {
            return iterator_count($element);
        } elseif (is_scalar($element)) {
            return strlen($element);
        } elseif (is_object($element)) {
            if (method_exists($element, 'size') && is_callable([$element, 'size'])) {
                return $element->size();
            } elseif (method_exists($element, 'count') && is_callable([$element, 'count'])) {
                return $element->count();
            } elseif (method_exists($element, 'length') && is_callable([$element, 'length'])) {
                return $element->length();
            }
        }

        return 0;
    }

    /**
     * @return TagToken|TextToken|VariableToken
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param TagToken|TextToken|VariableToken $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }
}
