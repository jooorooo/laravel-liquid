<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

class Math
{

    /**
     * addition
     *
     * @param float $input
     * @param float $operand
     *
     * @return float
     */
    public static function plus($input, $operand)
    {
        $input = is_numeric($input) ? $input : 0;
        $operand = is_numeric($operand) ? $operand : 0;
        return $input + $operand;
    }

    /**
     * subtraction
     *
     * @param int $input
     * @param int $operand
     *
     * @return int
     */
    public static function minus($input, $operand)
    {
        $input = is_numeric($input) ? $input : 0;
        $operand = is_numeric($operand) ? $operand : 0;
        return $input - $operand;
    }

    /**
     * @param mixed $input number
     *
     * @return int
     */
    public static function ceil($input)
    {
        if(is_numeric($input)) {
            return (int)ceil($input);
        }

        return 0;
    }

    /**
     * division
     *
     * @param int $input
     * @param int $operand
     *
     * @return int
     */
    public static function divided_by($input, $operand = null)
    {
        if(is_numeric($input) && is_numeric($operand) && (float)$operand !== (float)0) {
            return $input / $operand;
        }

        return 0;
    }

    /**
     * @param mixed $input number
     *
     * @return int
     */
    public static function floor($input)
    {
        if(is_numeric($input)) {
            return (int)floor($input);
        }

        return 0;
    }

    /**
     * modulo
     *
     * @param int|float $input
     * @param int|float $operand
     *
     * @return int|float
     */
    public static function modulo($input, $operand = null)
    {
        if(is_numeric($input) && is_numeric($operand)) {
            return fmod($input, $operand);
        }

        return 0;
    }

    /**
     * Round a number
     *
     * @param float $input
     * @param int $n precision
     *
     * @return float
     */
    public static function round($input, $n = 0)
    {
        if(is_numeric($input) && is_numeric($n)) {
            $input = round($input, (int)$n);
            if($n == 0) {
                return (int)$input;
            }
        }

        return is_numeric($input) ? (int)round($input) : 0;
    }

    /**
     * multiplication
     *
     * @param int|float $input
     * @param int|float $operand
     *
     * @return int|float
     */
    public static function times($input, $operand = null)
    {
        if(is_numeric($input) && is_numeric($operand)) {
            return $input * $operand;
        }

        return 0;
    }

}