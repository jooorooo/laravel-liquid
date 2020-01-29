<?php

namespace Liquid;

class Token
{
    protected $token;
    protected $offset;

    public function __construct($token, $offset)
    {
        $this->token = $token;
        $this->offset = $offset;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getOffset()
    {
        return $this->offset;
    }
}