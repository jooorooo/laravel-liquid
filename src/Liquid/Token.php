<?php

namespace Liquid;

class Token
{
    protected $type;
    protected $value;
    protected $lineno;

    public function __construct($type, $value, $lineno)
    {
        $this->type = $type;
        $this->value = $value;
        $this->lineno = $lineno;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->lineno;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}