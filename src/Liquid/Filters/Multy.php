<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Iterator;

class Multy
{

    /**
     * Return the size of an array or of an string
     *
     * @param mixed $input
     *
     * @return int
     */
    public static function size($input)
    {
        if ($input instanceof Iterator) {
            return iterator_count($input);
        }
        if (is_string($input) || is_numeric($input)) {
            return strlen($input);
        } elseif (is_array($input)) {
            return count($input);
        } elseif (is_object($input)) {
            if (method_exists($input, 'size')) {
                return $input->size();
            }
        }

        return $input;
    }

    /**
     * @param array|Iterator|string $input
     * @param int $offset
     * @param int $length
     *
     * @return array|Iterator|string
     */
    public static function slice($input, $offset, $length = null)
    {
        if ($input instanceof Iterator) {
            $input = iterator_to_array($input);
        }
        if (is_array($input)) {
            $input = array_slice($input, $offset, $length);
        } elseif (is_string($input)) {
            $input = $length === null
                ? substr($input, $offset)
                : substr($input, $offset, $length);
        }

        return $input;
    }


}