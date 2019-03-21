<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Illuminate\Support\Str AS IlluminateStr;

class Str
{

    /**
     * Add one string to another
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public static function append($input, $string)
    {
        return $input . $string;
    }

    /**
     * Prepend a string to another
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public static function prepend($input, $string)
    {
        return $string . $input;
    }

    /**
     * Convert an input to lowercase
     *
     * @param string $input
     *
     * @return string
     */
    public static function downcase($input)
    {
        return is_string($input) ? IlluminateStr::lower($input) : $input;
    }

    /**
     * Convert an input to uppercase
     *
     * @param string $input
     *
     * @return string
     */
    public static function upcase($input)
    {
        return is_string($input) ? IlluminateStr::upper($input) : $input;
    }

    /**
     * Removes html tags from text
     *
     * @param string $input
     *
     * @return string
     */
    public static function strip_html($input)
    {
        return is_string($input) ? strip_tags($input) : $input;
    }

    /**
     * Strip all newlines (\n, \r) from string
     *
     * @param string $input
     *
     * @return string
     */
    public static function strip_newlines($input)
    {
        return is_string($input) ? str_replace(array(
            "\n", "\r"
        ), '', $input) : $input;
    }

    /**
     * Truncate a string down to x characters
     *
     * @param string $input
     * @param int $characters
     * @param string $ending string to append if truncated
     *
     * @return string
     */
    public static function truncate($input, $characters = 100, $ending = '...')
    {
        if (is_string($input) || is_numeric($input)) {
            if (strlen($input) > $characters) {
                return substr($input, 0, $characters) . $ending;
            }
        }

        return $input;
    }


    /**
     * Truncate string down to x words
     *
     * @param string $input
     * @param int $words
     * @param string $ending string to append if truncated
     *
     * @return string
     */
    public static function truncatewords($input, $words = 15, $ending = '...')
    {
        return is_string($input) ? IlluminateStr::words($input, $words, $ending) : $input;
    }

    /**
     * Replace each newline (\n) with html break
     *
     * @param string $input
     *
     * @return string
     */
    public static function newline_to_br($input)
    {
        return is_string($input) ? str_replace(array(
            "\n", "\r"
        ), '<br />', $input) : $input;
    }

    /**
     * Replace occurrences of a string with another
     *
     * @param string $input
     * @param string $string
     * @param string $replacement
     *
     * @return string
     */
    public static function replace($input, $string, $replacement = '')
    {
        return str_replace($string, $replacement, $input);
    }


    /**
     * Replace the first occurrences of a string with another
     *
     * @param string $input
     * @param string $string
     * @param string $replacement
     *
     * @return string
     */
    public static function replace_first($input, $string, $replacement = '')
    {
        return IlluminateStr::replaceFirst($string, $replacement, $input);
    }

    /**
     * Remove a substring
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public static function remove($input, $string)
    {
        return str_replace($string, '', $input);
    }


    /**
     * Remove the first occurrences of a substring
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public static function remove_first($input, $string)
    {
        return static::replace_first($string, $input);
    }

    /**
     * Split input string into an array of substrings separated by given pattern.
     *
     * @param string $input
     * @param string $pattern
     *
     * @return array
     */
    public static function split($input, $pattern)
    {
        // Unlike PHP explode function, empty string after split filtering is empty array in Liquid.
        if (!is_string($input) || $input === '') {
            return array();
        }
        return explode($pattern, $input);
    }

    /**
     * Capitalize words in the input sentence
     *
     * @param string $input
     *
     * @return string
     */
    public static function capitalize($input)
    {
        return is_string($input) ? IlluminateStr::title($input) : $input;
    }

    /**
     * Camelize text
     *
     * @param string $input
     *
     * @return string
     */
    public static function camelize($input)
    {
        return is_string($input) ? IlluminateStr::camel($input) : $input;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public static function lstrip($input)
    {
        return ltrim($input);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public static function rstrip($input)
    {
        return rtrim($input);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public static function strip($input)
    {
        return trim($input);
    }

    /**
     * URL encodes a string
     *
     * @param string $input
     *
     * @return string
     */
    public static function url_encode($input)
    {
        return urlencode($input);
    }

}