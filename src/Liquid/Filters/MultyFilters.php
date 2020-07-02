<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Iterator;
use Illuminate\Support\Str AS IlluminateStr;
use Liquid\Exceptions\BaseFilterError;
use Liquid\Exceptions\FilterError;

class MultyFilters extends AbstractFilters
{
    /**
     * Return the size of an array or of an string
     *
     * @param mixed $input
     *
     * @return int
     */
    public function size($input)
    {
        return $this->context->getSize($input);
    }

    /**
     * @param array|Iterator|string $input
     * @param int $offset
     * @param int $length
     *
     * @return array|Iterator|string
     */
    public function slice($input, $offset = 0, $length = 1)
    {
        try {
            $this->__validate(func_get_args(), 2, [
                1 => 'nInt',
                2 => 'nInt',
            ]);

            if (is_array($input)) {
                $input = array_slice($input, $offset, $length);
            } elseif (is_string($input) || is_numeric($input)) {
                $input = IlluminateStr::substr($input, $offset, $length);
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
     * @param mixed $input
     *
     * @return string
     */
    public function json($input)
    {
        return json_encode($input);
    }

}
