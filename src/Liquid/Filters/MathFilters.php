<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Liquid\Exceptions\BaseFilterError;
use Liquid\Exceptions\FilterError;
use Liquid\Exceptions\FilterValidateError;

class MathFilters extends AbstractFilters
{

    /**
     * addition
     *
     * @param $input
     *
     * @return float
     */
    public function abs($input)
    {
        return is_numeric($input) ? abs($input) : 0;
    }

    /**
     * addition
     *
     * @param $input
     *
     * @return float
     */
    public function at_most(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'numeric',
            ]);

            $input = [
                floatVal(array_shift($input)),
                floatVal(array_shift($input)),
            ];

            return min($input);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * addition
     *
     * @param $input
     *
     * @return float
     */
    public function at_least(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'numeric',
            ]);

            $input = [
                floatVal(array_shift($input)),
                floatVal(array_shift($input)),
            ];

            return max($input);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * @param mixed $input number
     *
     * @return int
     */
    public function ceil($input)
    {
        if(is_numeric($input)) {
            return (int)ceil($input);
        }

        return 0;
    }

    /**
     * division
     *
     * @param $input
     *
     * @return int
     */
    public function divided_by(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'numeric',
            ]);

            $value = floatVal(array_shift($input));
            $operand = floatVal(array_shift($input));

            return $operand != 0 ? $value / $operand : 0;
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * @param mixed $input number
     *
     * @return int
     */
    public function floor($input)
    {
        if(is_numeric($input)) {
            return (int)floor($input);
        }

        return 0;
    }

    /**
     * addition
     *
     * @param $input
     *
     * @return float
     */
    public function plus(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'numeric',
            ]);

            $input = [
                floatVal(array_shift($input)),
                floatVal(array_shift($input)),
            ];

            return array_sum($input);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * subtraction
     *
     * @param $input
     *
     * @return int
     */
    public function minus(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'numeric',
            ]);

            $input = [
                floatVal(array_shift($input)),
                floatVal(array_shift($input)) * -1,
            ];

            return array_sum($input);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * Round a number
     *
     * @param $input
     *
     * @param int $precision
     * @return float
     */
    public function round($input, $precision = 0)
    {
        try {
            if(!preg_match('/^\d+$/', $precision)) {
                throw new FilterValidateError(
                    'filter requires an integer argument'
                );
            }

            $input = round(floatVal($input), $precision);
            if($precision == 0) {
                $input = (int)$input;
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
     * multiplication
     *
     * @param $input
     *
     * @return int|float
     */
    public function times(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'numeric',
            ]);

            $input = [
                floatVal(array_shift($input)),
                floatVal(array_shift($input)),
            ];

            return $input[0] * $input[1];
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    /**
     * modulo
     *
     * @param $input
     *
     * @return int|float
     */
    public function modulo(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'numeric',
            ]);

            $input = [
                floatVal(array_shift($input)),
                floatVal(array_shift($input)),
            ];

            return fmod($input[0], $input[1]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

}
