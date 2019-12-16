<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use ArrayAccess;
use Iterator;
use Liquid\Context;
use Liquid\Drop;
use Traversable;

class Arr
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context = null)
    {
        $this->context = $context;
    }

    /**
     * Joins elements of an array with a given character between them
     *
     * @param array|Traversable $input
     * @param string $glue
     *
     * @return string
     */
    public function join($input, $glue = ' ')
    {
        if ($input instanceof Traversable) {
            $str = '';
            foreach ($input as $elem) {
                if ($str) {
                    $str .= $glue;
                }
                $str .= $elem;
            }
            return $str;
        }
        return is_array($input) ? implode($glue, $input) : $input;
    }

    /**
     * Returns the first element of an array
     *
     * @param array|\Iterator $input
     *
     * @return mixed
     */
    public function first($input)
    {
        if ($input instanceof Iterator) {
            $input->rewind();
            return $input->current();
        }
        return is_array($input) ? reset($input) : $input;
    }

    /**
     * Returns the last element of an array
     *
     * @param array|Traversable $input
     *
     * @return mixed
     */
    public function last($input)
    {
        if ($input instanceof Traversable) {
            $last = null;
            foreach ($input as $elem) {
                $last = $elem;
            }
            return $last;
        }
        return is_array($input) ? end($input) : $input;
    }

    /**
     * Map/collect on a given property
     *
     * @param array|Traversable $input
     * @param string $property
     *
     * @return string
     */
    public function map($input, $property = null)
    {
        if ($input instanceof Traversable) {
            $input = iterator_to_array($input);
        }

        if (!is_array($input)) {
            return $input;
        }

        return array_map(function ($elem) use ($property) {
            return $this->context->getValue($elem, $property);
        }, $input);
    }

    /**
     * Reverse the elements of an array
     *
     * @param array|Traversable $input
     *
     * @return array
     */
    public function reverse($input)
    {
        if(is_scalar($input)) {
            return $input;
        }

        if ($input instanceof Traversable) {
            $input = iterator_to_array($input);
        }
        return array_reverse($input);
    }


    /**
     * Sort the elements of an array
     *
     * @param array|Traversable $input
     * @param string $property use this property of an array element
     *
     * @return array
     */
    public function sort($input, $property = null)
    {
        if(is_scalar($input)) {
            return $input;
        }

        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        if ($property === null) {
            asort($input);
        } else {
            $first = reset($input);

            if ($first !== false && $this->context->hasGetValue($first, $property)) {
                uasort($input, function ($a, $b) use ($property) {
                    $valueA = $this->context->getValue($a, $property);
                    $valueB = $this->context->getValue($b, $property);
                    if ($valueA == $valueB) {
                        return 0;
                    }

                    return $valueA < $valueB ? -1 : 1;
                });
            }
        }

        return $input;
    }

    /**
     * Remove duplicate elements from an array
     *
     * @param array|\Traversable $input
     *
     * @return array
     */
    public function uniq($input)
    {
        if(is_scalar($input)) {
            return $input;
        }

        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        return array_unique($input);
    }

    /**
     * Split an array into chunks
     *
     * @param array|\Traversable $input
     *
     * @return array
     */
    public function chunk($input, $size = null)
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        }

        if(!is_numeric($size)) {
            return [];
        }

        return is_array($input) ? array_chunk($input, $size) : [];
    }

}