<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Illuminate\Support\Str AS IlluminateStr;
use Liquid\Exceptions\BaseFilterError;
use Liquid\Exceptions\FilterError;

class StrFilters extends AbstractFilters
{

    /**
     * Add one string to another
     *
     * @param $input
     *
     * @return string
     */
    public function append(...$input)
    {
        try {
            $this->__validate($input, 2);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1])) {
                return $input[0];
            }

            return $input[0] . $input[1];
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Prepend a string to another
     *
     * @param $input
     *
     * @return string
     */
    public function prepend(...$input)
    {
        try {
            $this->__validate($input, 2);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1])) {
                return $input[0];
            }

            return $input[1] . $input[0];
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
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
        $inputNew = is_array($input) ? json_encode($input) : $input;
        return $this->__isString($inputNew) ? IlluminateStr::lower($inputNew) : $input;
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
        $inputNew = is_array($input) ? json_encode($input) : $input;
        return $this->__isString($inputNew) ? IlluminateStr::upper($inputNew) : $input;
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
        $inputNew = is_array($input) ? json_encode($input) : $input;
        return $this->__isString($inputNew) ? strip_tags($inputNew) : $input;
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
        $inputNew = is_array($input) ? json_encode($input) : $input;
        return $this->__isString($inputNew) ? str_replace(array(
            "\n", "\r"
        ), '', $inputNew) : $input;
    }

    /**
     * Truncate a string down to x characters
     *
     * @param $input
     *
     * @return string
     */
    public function truncate(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'int',
            ]);

            if(!isset($input[2]) || !$this->__isString($input[2])) {
                $input[2] = '...';
            }

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0])) {
                return $input[0];
            }

            return IlluminateStr::substr($input[0], 0, $input[1]) . $input[2];
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }


    /**
     * Truncate string down to x words
     *
     * @param $input
     *
     * @return string
     */
    public function truncatewords(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'int',
            ]);

            if(!isset($input[2]) || !$this->__isString($input[2])) {
                $input[2] = '...';
            }

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0])) {
                return $input[0];
            }

            return IlluminateStr::words($input[0], $input[1], $input[2]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
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
        $inputNew = is_array($input) ? json_encode($input) : $input;
        return $this->__isString($inputNew) ? str_replace(array(
            "\n", "\r"
        ), '<br />', $inputNew) : $input;
    }

    /**
     * Replace occurrences of a string with another
     *
     * @param $input
     *
     * @return string
     */
    public function replace(...$input)
    {
        try {
            $this->__validate($input, 3);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1]) || !$this->__isString($input[2])) {
                return $input[0];
            }

            return str_replace($input[1], $input[2], $input[0]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Replace the first occurrences of a string with another
     *
     * @param $input
     *
     * @return string
     */
    public function replace_first(...$input)
    {
        try {
            $this->__validate($input, 3);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1]) || !$this->__isString($input[2])) {
                return $input[0];
            }

            return IlluminateStr::replaceFirst($input[1], $input[2], $input[0]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Remove a substring
     *
     * @param $input
     *
     * @return string
     */
    public function remove(...$input)
    {
        try {
            $this->__validate($input, 2);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1])) {
                return $input[0];
            }

            return str_replace($input[1], '', $input[0]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }


    /**
     * Remove the first occurrences of a substring
     *
     * @param $input
     *
     * @return string
     */
    public function remove_first(...$input)
    {
        try {
            $this->__validate($input, 2);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1])) {
                return $input[0];
            }

            return IlluminateStr::replaceFirst($input[1], '', $input[0]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Replace the first occurrences of a string with another
     *
     * @param $input
     *
     * @return string
     */
    public function replace_last(...$input)
    {
        try {
            $this->__validate($input, 3);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1]) || !$this->__isString($input[2])) {
                return $input[0];
            }

            return IlluminateStr::replaceLast($input[1], $input[2], $input[0]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Remove the first occurrences of a substring
     *
     * @param $input
     *
     * @return string
     */
    public function remove_last(...$input)
    {
        try {
            $this->__validate($input, 2);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1])) {
                return $input[0];
            }

            return IlluminateStr::replaceLast($input[1], '', $input[0]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Split input string into an array of substrings separated by given pattern.
     *
     * @param $input
     *
     * @return array
     */
    public function split(...$input)
    {
        try {
            $this->__validate($input, 2);

            $input = array_map(function($input) {
                return is_array($input) ? json_encode($input) : $input;
            }, $input);

            if(!$this->__isString($input[0]) || !$this->__isString($input[1])) {
                return [];
            }

            if ($input[0] === '') {
                return [];
            }

            return empty($input[1]) ? str_split($input[0]) : explode($input[1], $input[0]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
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
        $inputNew = is_array($input) ? json_encode($input) : $input;
        return $this->__isString($inputNew) ? IlluminateStr::title($inputNew) : $input;
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
        $inputNew = is_array($input) ? json_encode($input) : $input;
        return $this->__isString($inputNew) ? IlluminateStr::camel($inputNew) : $input;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function lstrip($input)
    {
        $inputNew = is_array($input) ? json_encode($input) : $input;
        if(!$this->__isString($inputNew)) {
            return $input;
        }

        return ltrim($inputNew);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function rstrip($input)
    {
        $inputNew = is_array($input) ? json_encode($input) : $input;
        if(!$this->__isString($inputNew)) {
            return $input;
        }

        return rtrim($inputNew);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function strip($input)
    {
        $inputNew = is_array($input) ? json_encode($input) : $input;
        if(!$this->__isString($inputNew)) {
            return $input;
        }

        return trim($inputNew);
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
        $inputNew = is_array($input) ? json_encode($input) : $input;
        if(!$this->__isString($inputNew)) {
            return $input;
        }

        return urlencode($inputNew);
    }

}
