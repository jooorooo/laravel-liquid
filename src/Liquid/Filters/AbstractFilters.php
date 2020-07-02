<?php

namespace Liquid\Filters;

use Liquid\Context;
use Liquid\Exceptions\FilterError;
use Liquid\Exceptions\FilterValidateError;

abstract class AbstractFilters
{
    /**
     * @var Context
     */
    protected $context;

    final public function __construct(Context $context)
    {
        $this->context = $context;
    }

    final protected function __validate($parameters, int $total_parameters, array $validation = null)
    {
        if(($given = count($parameters)) != $total_parameters) {
            throw new FilterValidateError(sprintf(
                'wrong number of arguments (given %d, expected %d)',
                $given - 1,
                $total_parameters - 1
            ));
        }

        if($validation) {
            array_map(function($parameter, $key) use($validation) {
                if(!empty($validation[$key]) && ($rules = explode('|', $validation[$key]))) {
                    array_map(function($rule) use($parameter) {
                        if(method_exists($this, $method = sprintf('__validate%s', $rule))) {
                            $this->$method($parameter);
                        }
                    }, $rules);
                }
            }, $parameters, array_keys($parameters));
        }
    }

    private function __validateArray($input)
    {
        if(!is_array($input)) {
            throw new FilterValidateError(
                'filter requires an array argument'
            );
        }
    }

    private function __validateScalar($input)
    {
        if(!is_scalar($input)) {
            throw new FilterValidateError(
                'filter requires an scalar argument'
            );
        }
    }

    private function __validateNumeric($input)
    {
        if(!is_numeric($input)) {
            throw new FilterValidateError(
                'filter requires an numeric argument'
            );
        }
    }

    private function __validateInt($input)
    {
        if(!preg_match('/^\d+$/', $input)) {
            throw new FilterValidateError(
                'filter requires an integer argument'
            );
        }
    }

}
