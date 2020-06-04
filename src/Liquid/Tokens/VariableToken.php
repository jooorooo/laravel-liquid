<?php

namespace Liquid\Tokens;

class VariableToken extends GuessToken
{

    protected $variable;
    protected $filters = [];

    public function __construct($offset, $code, $source, $variable, array $filters = [])
    {
        parent::__construct($offset, $code, $source);
        $this->variable = $variable;
        $this->filters = $filters;
    }

    /**
     * @return string
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }
}
