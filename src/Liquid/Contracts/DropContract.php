<?php

namespace Liquid\Contracts;

use Liquid\Context;

interface DropContract
{

    /**
     * @param Context $context
     */
    public function setContext(Context $context);

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     *
     * @return null
     */
    public function beforeMethod($method);

    /**
     * Invoke a specific method
     *
     * @param string $method
     *
     * @return mixed
     */
    public function invokeDrop($method);

    /**
     * Returns true if the drop supports the given method
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasKey($name);

    /**
     * Returns string. Object name
     *
     * @return string
     */
    public function __toString();

}
