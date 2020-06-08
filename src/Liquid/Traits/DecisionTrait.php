<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 10:09 ч.
 */

namespace Liquid\Traits;

use Liquid\Context;
use Liquid\LiquidException;
use Liquid\Variable;

trait DecisionTrait
{
    /**
     * The current left variable to compare
     *
     * @var string
     */
    public $left;

    /**
     * The current right variable to compare
     *
     * @var string
     */
    public $right;

    /**
     * Returns a string value of an array for comparisons
     *
     * @param mixed $value
     *
     * @return string
     * @throws LiquidException
     */
    private function stringValue($value)
    {
        // Objects should have a __toString method to get a value to compare to
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string)$value;
            } else {
                throw new LiquidException("Cannot convert $value to string"); // harry
            }
        }

        // Arrays simply return true
        if (is_array($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * Check to see if to variables are equal in a given context
     *
     * @param string $left
     * @param string $right
     * @param Context $context
     *
     * @return bool
     * @throws LiquidException
     */
    protected function equalVariables($left, $right, Context $context)
    {
        $left = $this->stringValue($context->get($left));
        $right = $this->stringValue($context->get($right));

        return ($left == $right);
    }

    /**
     * Interpret a comparison
     *
     * @param string $left
     * @param string $right
     * @param string $op
     * @param Context $context
     *
     * @return bool
     * @throws LiquidException
     */
    protected function interpretCondition($left, $right, $op, Context $context)
    {
        if (is_null($op)) {
            $reverse = false;
            if(substr($left, 0, 1) == '!') {
                $left = substr($left, 1);
                $reverse = true;
            }

            $value = $this->getValue($left, $context);

            $value = $this->stringValue($value);
            return $reverse ? !$value : $value;
        }

        // values of 'empty' have a special meaning in array comparisons
        if ($right == 'empty' && ($c = $this->getValue($left, $context)) && is_array($c)) {
            $left = count($this->getValue($left, $context));
            $right = 0;
        } elseif ($left == 'empty' && ($c = $this->getValue($right, $context)) && is_array($c)) {
            $right = count($this->getValue($right, $context));
            $left = 0;
        } elseif ($right == 'blank') {
            $c = $this->getValue($left, $context);
            return $this->blankOperationCompare($c, $op, '@');
        } elseif ($left == 'blank') {
            $c = $this->getValue($right, $context);
            return $this->blankOperationCompare('@', $op, $c);
        } else {

            $left = $this->getValue($left, $context);
            $right = $this->getValue($right, $context);

            $left = $this->stringValue($left);
            $right = $this->stringValue($right);
        }

        // special rules for null values
        if (is_null($left) || is_null($right)) {
            // null == null returns true
            if ($op == '==' && is_null($left) && is_null($right)) {
                return true;
            }

            // null != anything other than null return true
            if ($op == '!=' && (!is_null($left) || !is_null($right))) {
                return true;
            }

            // everything else, return false;
            return false;
        }

        // regular rules
        switch ($op) {
            case '==':
                return ($left == $right);

            case '!=':
                return ($left != $right);

            case '>':
                return ($left > $right);

            case '<':
                return ($left < $right);

            case '>=':
                return ($left >= $right);

            case '<=':
                return ($left <= $right);

            case 'contains':
                return is_array($left) ? in_array($right, $left) : (strpos($left, $right) !== false);

            default:
                throw new LiquidException("Error in tag '" . $this->name() . "' - Unknown operator $op");
        }
    }

    protected function getValue($text, Context $context)
    {
        if(is_numeric($text)) {
            if(($check = filter_var($text, FILTER_VALIDATE_INT)) !== false) {
                return $check;
            }
            return (float)$text;
        }

        $var = new Variable($text, $this->compiler);
        return $var->render($context);
    }

    /**
     * Interpret a comparison for blank
     *
     * @param string $left
     * @param string $op
     * @param string $right
     * @param Context $context
     *
     * @return bool
     * @throws LiquidException
     */
    protected function blankOperationCompare($left, $op, $right)
    {
        // regular rules
        switch ($op) {
            case '==':
                return $left == '@' ? empty($right) : empty($left);

            case '!=':
                return $left == '@' ? !empty($right) : !empty($left);

            default:
                throw new LiquidException("Error in tag '" . $this->name() . "' - Unknown operator $op");
        }
    }
}
