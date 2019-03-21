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
        return (int)ceil((float)$input);
    }

    /**
     * division
     *
     * @param int $input
     * @param int $operand
     *
     * @return int
     */
    public static function divided_by($input, $operand)
    {
        return (int)$input / (int)$operand;
    }

    /**
     * @param mixed $input number
     *
     * @return int
     */
    public static function floor($input)
    {
        return (int)floor((float)$input);
    }

    /**
     * modulo
     *
     * @param int $input
     * @param int $operand
     *
     * @return int
     */
    public static function modulo($input, $operand)
    {
        return (int)$input % (int)$operand;
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
        return round((float)$input, (int)$n);
    }

    /**
     * multiplication
     *
     * @param int $input
     * @param int $operand
     *
     * @return int
     */
    public static function times($input, $operand)
    {
        return (int)$input * (int)$operand;
    }

}