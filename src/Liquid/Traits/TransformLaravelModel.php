<?php

namespace Liquid\Traits;

use Illuminate\Database\Eloquent\Model;

trait TransformLaravelModel
{

    protected function transformModel(array $collection)
    {
        return array_map(function($object) {
            if($object instanceof Model && ($transform = config('liquid.transform_model.' . get_class($object))) && is_callable($transform)) {
                return call_user_func($transform, $object);
            }

            return $object;
        }, $collection);
    }

    /**
     * Validate limit for Pagination and For tag
     *
     * @param integer $number
     *
     * @return integer
     *
     */
    protected function validateNumberItems($number)
    {
        if($number < 1 || $number > 1000) {
            return 50;
        }

        return (int)$number;
    }

    /**
     * Validate offset for For tag
     *
     * @param integer $number
     *
     * @return integer
     *
     */
    protected function validateOffset($number)
    {
        if($number < 0) {
            return 0;
        }

        return (int)$number;
    }

}