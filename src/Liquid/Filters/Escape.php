<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

class Escape
{
    /**
     * Escape a string
     *
     * @param string $input
     *
     * @return string
     */
    public static function escape($input)
    {
        return is_string($input) ? htmlentities($input, ENT_QUOTES) : $input;
    }

    /**
     * Pseudo-filter: negates auto-added escape filter
     *
     * @param string $input
     *
     * @return string
     */
    public static function raw($input)
    {
        return $input;
    }
    
    /**
     * Escape a string once, keeping all previous HTML entities intact
     *
     * @param string $input
     *
     * @return string
     */
    public static function escape_once($input)
    {
        return is_string($input) ? htmlentities($input, ENT_QUOTES, null, false) : $input;
    }

}