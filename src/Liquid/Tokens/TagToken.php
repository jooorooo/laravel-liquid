<?php

namespace Liquid\Tokens;

class TagToken extends GuessToken
{

    protected $tag;
    protected $token;
    protected $filters = [];
    protected $parameters;

    public function __construct($offset, $code, $source, $tag, $token, $parameters, array $filters = [])
    {
        parent::__construct($offset, $code, $source);
        $this->tag = $tag;
        $this->token = $token;
        $this->filters = $filters;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
