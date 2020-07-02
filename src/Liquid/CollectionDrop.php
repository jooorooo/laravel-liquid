<?php

namespace Liquid;

use ArrayAccess;
use ArrayIterator;
use Illuminate\Contracts\Support\Arrayable;
use Liquid\Contracts\DropCollectionContract;

class CollectionDrop extends ArrayIterator implements DropCollectionContract
{

    public function __construct($array = [], $flags = 0)
    {
        if($array instanceof Arrayable) {
            $array = $array->all();
        }

        parent::__construct(is_array($array) ? $array : [], $flags);
    }

    public static function make($data)
    {
        return new static($data);
    }

    /**
     * @var Context
     */
    protected $context;

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     *
     * @return null
     */
    public function beforeMethod($method)
    {
        return null;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Invoke a specific method
     *
     * @param string $method
     *
     * @return mixed
     */
    public function invokeDrop($method)
    {
        $result = $this->beforeMethod($method);

        if (is_null($result) && is_callable(array($this, $method))) {
            $result = $this->$method();
        }

        return $result;
    }

    /**
     * Returns true if the drop supports the given method
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasKey($name)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return 'CollectionDrop';
    }

    /**
     * @inheritDoc
     */
    public function merge($data)
    {
        if(is_array($data) || $data instanceof DropCollectionContract) {
            foreach($data as $key => $value) {
                $this->offsetSet($key, $value);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function all()
    {
        return iterator_to_array ($this, true);
    }
}
