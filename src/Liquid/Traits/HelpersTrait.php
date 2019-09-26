<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 10:21 ч.
 */

namespace Liquid\Traits;


trait HelpersTrait
{

    /**
     * Flatten a multidimensional array into a single array. Does not maintain keys.
     *
     * @param array $array
     *
     * @return array
     */
    public function arrayFlatten($array)
    {
        $return = array();

        foreach ($array as $element) {
            if (is_array($element)) {
                $return = array_merge($return, $this->arrayFlatten($element));
            } else {
                $return[] = $element;
            }
        }
        return $return;
    }

    /**
     * All values in PHP Liquid are truthy except null and false.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isTruthy($value)
    {
        return !$this->isFalsy($value);
    }

    /**
     * The falsy values in PHP Liquid are null and false.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isFalsy($value)
    {
        return $value === false || $value === null || $value === '' || $value === 0 || $value === [];
    }

}