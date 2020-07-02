<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Liquid\Contracts\DropCollectionContract;
use Liquid\Exceptions\BaseFilterError;
use Liquid\Exceptions\FilterError;
use Traversable;

class ArrFilters extends AbstractFilters
{

    /**
     * Joins elements of an array with a given character between them
     *
     * @param array $input
     *
     * @return string
     */
    public function join(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'scalar',
            ]);

            if(!is_array($input[0]) && !($input[0] instanceof DropCollectionContract)) {
                $input[0] = [$input[0]];
            }

            return implode($input[1], $input[0]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Returns the first element of an array
     *
     * @param array $input
     *
     * @return mixed
     */
    public function first($input)
    {
        if(is_array($input)) {
            return Arr::first($input);
        } elseif(is_scalar($input)) {
            return Str::substr($input, 0, 1);
        }

        return null;
    }

    /**
     * Returns the last element of an array
     *
     * @param array $input
     *
     * @return mixed
     */
    public function last($input)
    {
        if(is_array($input)) {
            return end($input);
        } elseif(is_scalar($input)) {
            return Str::substr($input, -1);
        }

        return null;
    }

    /**
     * Concat array
     *
     * @param array $input
     *
     * @return array
     */
    public function concat(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'array',
            ]);

            if(!is_array($input[0]) && !($input[0] instanceof DropCollectionContract)) {
                $input[0] = [$input[0]];
            }

            $className = is_object($input[0]) && $input[0] instanceof DropCollectionContract ? get_class($input[0]) : null;

            $input = array_map(function($input) {
                return $input instanceof DropCollectionContract ? $input->all() : $input;
            }, $input);

            $input = call_user_func_array('array_merge', $input);
            if($className) {
                $input = new $className($input);
            }

            return $input;
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Map/collect on a given property
     *
     * @param array $input
     *
     * @return array
     */
    public function map(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'scalar',
            ]);

            if(!is_array($input[0]) && !($input[0] instanceof DropCollectionContract)) {
                return null;
            }

            if($input[0] instanceof DropCollectionContract) {
                $input[0] = $input[0]->all();
            }

            $input[0] = array_map(function ($elem) use ($input) {
                return $this->context->getValue($elem, $input[1]);
            }, $input[0]);

            return $input[0];
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Reverse the elements of an array
     *
     * @param array $input
     *
     * @return array|string
     */
    public function reverse($input)
    {
        if(is_array($input)) {
            return array_reverse($input);
        }

        return $input;
    }


    /**
     * Sort the elements of an array
     *
     * @param $input
     *
     * @return array|string
     */
    public function sort(...$input)
    {
        if(!is_array($input[0]) && !($input[0] instanceof DropCollectionContract)) {
            return $input[0];
        }

        if($className = is_object($input[0]) && $input[0] instanceof DropCollectionContract ? get_class($input[0]) : null) {
            $input[0] = $input[0]->all();
        }

        if (is_null($input[1] = ($input[1] ?? null))) {
            asort($input[0]);
        } else {
            $first = reset($input[0]);

            if ($first !== false && $this->context->hasGetValue($first, $input[1])) {
                uasort($input[0], function ($a, $b) use ($input) {
                    $valueA = $this->context->getValue($a, $input[1]);
                    $valueB = $this->context->getValue($b, $input[1]);
                    if ($valueA == $valueB) {
                        return 0;
                    }

                    return $valueA < $valueB ? -1 : 1;
                });
            }
        }

        if($className) {
            $input[0] = new $className($input[0]);
        }

        return $input[0];
    }


    /**
     * Filter elements of an array
     *
     * @param $input
     *
     * @return array|string
     */
    public function where(...$input)
    {
        try {
            $this->__validate($input, 3, [
                1 => 'scalar',
                2 => 'scalar',
            ]);

            return array_filter($input[0], function($context) use($input) {
                return $this->context->getValue($context, $input[1]) === $input[2];
            });
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Remove duplicate elements from an array
     *
     * @param array|Traversable $input
     *
     * @return array|string
     */
    public function uniq($input)
    {
        if(is_array($input) || $input instanceof DropCollectionContract) {
            return array_unique($input instanceof DropCollectionContract ? $input->all() : $input);
        }

        return $input;
    }

    /**
     * Split an array into chunks
     *
     * @param $input
     *
     * @return array
     */
    public function chunk(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'int',
            ]);

            if(!is_array($input[0]) && !($input[0] instanceof DropCollectionContract)) {
                return $input[0];
            }

            return array_chunk($input[0], $input[1]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

}
