<?php

namespace Liquid\Contracts;

interface DropCollectionContract extends DropContract
{
    /**
     * @param mixed $data
     */
    public function merge($data);
    /**
     * @return array
     */
    public function all();
}
