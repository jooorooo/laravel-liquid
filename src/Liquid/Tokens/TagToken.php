<?php

namespace Liquid\Tokens;

class TagToken extends GuessToken
{

    protected $tag;
    protected $token;
    protected $filters = [];

    public function __construct($offset, $code, $source, $tag, $token, array $filters = [])
    {
        parent::__construct($offset, $code, $source);
        $this->tag = $tag;
        $this->token = $token;
        $this->filters = $filters;
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
}
