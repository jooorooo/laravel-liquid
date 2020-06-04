<?php

use Liquid\Factory;

if (!function_exists('liquid')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return Factory
     */
    function liquid($view = null, $data = [], $mergeData = [])
    {
        $factory = app('liquid.factory');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}
