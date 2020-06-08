<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Illuminate\Support\Str AS IlluminateStr;

class Str extends AbstractFilters
{

    /**
     * Add one string to another
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public function append($input, $string)
    {
        if(!is_scalar($input) || !is_scalar($string)) {
            return $input;
        }

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
    public function prepend($input, $string)
    {
        if(!is_scalar($input) || !is_scalar($string)) {
            return $input;
        }

        return $string . $input;
    }

    /**
     * Convert an input to lowercase
     *
     * @param string $input
     *
     * @return string
     */
    public function downcase($input)
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
    public function upcase($input)
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
    public function strip_html($input)
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
    public function strip_newlines($input)
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
    public function truncate($input, $characters = 100, $ending = '...')
    {
        if (is_scalar($input) && IlluminateStr::length($input) > $characters) {
            return IlluminateStr::substr($input, 0, $characters) . $ending;
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
    public function truncatewords($input, $words = 15, $ending = '...')
    {
        return is_scalar($input) ? IlluminateStr::words($input, $words, $ending) : $input;
    }

    /**
     * Replace each newline (\n) with html break
     *
     * @param string $input
     *
     * @return string
     */
    public function newline_to_br($input)
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
    public function replace($input, $string, $replacement = '')
    {
        if(!is_scalar($input) || !is_scalar($string) || !is_scalar($replacement)) {
            return $input;
        }

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
    public function replace_first($input, $string, $replacement = '')
    {
        if(!is_scalar($input) || !is_scalar($string) || !is_scalar($replacement)) {
            return $input;
        }

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
    public function remove($input, $string)
    {
        if(!is_scalar($input) || !is_scalar($string)) {
            return $input;
        }

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
    public function remove_first($input, $string)
    {
        if(!is_scalar($input) || !is_scalar($string)) {
            return $input;
        }

        return static::replace_first($input, $string);
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
    public function replace_last($input, $string, $replacement = '')
    {
        if(!is_scalar($input) || !is_scalar($string) || !is_scalar($replacement)) {
            return $input;
        }

        return IlluminateStr::replaceLast($string, $replacement, $input);
    }

    /**
     * Remove the first occurrences of a substring
     *
     * @param string $input
     * @param string $string
     *
     * @return string
     */
    public function remove_last($input, $string)
    {
        if(!is_scalar($input) || !is_scalar($string)) {
            return $input;
        }

        return static::replace_last($input, $string);
    }

    /**
     * Split input string into an array of substrings separated by given pattern.
     *
     * @param string $input
     * @param string $pattern
     *
     * @return array
     */
    public function split($input, $pattern)
    {
        if(!is_scalar($input) || !is_scalar($pattern)) {
            return array();
        }

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
    public function capitalize($input)
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
    public function camelize($input)
    {
        return is_string($input) ? IlluminateStr::camel($input) : $input;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function lstrip($input)
    {
        if(!is_scalar($input)) {
            return $input;
        }

        return ltrim($input);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function rstrip($input)
    {
        if(!is_scalar($input)) {
            return $input;
        }

        return rtrim($input);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function strip($input)
    {
        if(!is_scalar($input)) {
            return $input;
        }

        return trim($input);
    }

    /**
     * URL encodes a string
     *
     * @param string $input
     *
     * @return string
     */
    public function url_encode($input)
    {
        if(!is_scalar($input)) {
            return $input;
        }

        return urlencode($input);
    }

}
