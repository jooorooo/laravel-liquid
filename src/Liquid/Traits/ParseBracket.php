<?php

namespace Liquid\Traits;

use Liquid\Context;
use Liquid\Exceptions\SyntaxError;
use Liquid\LiquidCompiler;
use Liquid\Regexp;

trait ParseBracket
{

    use HelpersTrait, DecisionTrait;

    /**
     * @var array
     */
    protected $conditional_operators = [
        '==', '!=', '>=', '<=',  '>', '<', 'contains'
    ];

    /**
     * @param $string
     * @param Context $context
     * @return string
     * @throws SyntaxError
     */
    protected function recursiveReplaceBracket($string, Context $context)
    {
        return $this->parseLogicalExpresion(preg_replace_callback("/\((([^()]*|(?R))*)\)/", function($match) use($string, $context) {
            if(strpos($match[1], '(') !== false && strpos($match[1], ')') !== false) {
                $result = $this->recursiveReplaceBracket($match[1], $context);
                if(strpos($result, '(') === false && strpos($result, ')') === false) {
                    $result = $this->parseLogicalExpresion($result, $context);
                } else {
                    $result = $this->recursiveReplaceBracket($result, $context);
                }
                return $this->recursiveReplaceBracket(sprintf('(%s)', $result), $context);
            } else {
                return $this->parseLogicalExpresion($match[1], $context);
            }
        }, $string), $context);
    }

    /**
     * @param $string
     * @param Context $context
     * @return string
     * @throws SyntaxError
     */
    protected function parseLogicalExpresion($string, Context $context)
    {
        $logicalRegex = new Regexp('/\s+(and|or|\|\||\&\&)\s+/i');
        $co = str_replace([
            '>', '<', '!'
        ], [
            '\>', '\<', '\!'
        ], implode('|', $this->conditional_operators));

        //$conditionalRegex = new Regexp('/(' . LiquidCompiler::QUOTED_FRAGMENT . ')\s*([=!<>a-z_]+)?\s*(' . LiquidCompiler::QUOTED_FRAGMENT . ')?/');
        $conditionalRegex = new Regexp('/^\s*(((?!(' . $co . ')).)*)\s*(' . $co . ')?\s*(' . LiquidCompiler::QUOTED_FRAGMENT . ')?\s*$/');

        // Extract logical operators
        $logicalRegex->matchAll($string);

        $logicalOperators = $logicalRegex->matches;
        $logicalOperators = array_merge(array('and'), $logicalOperators[1]);
        // Extract individual conditions
        $temp = $logicalRegex->split($string);

        $conditions = array();

        foreach ($temp as $condition) {
            if ($conditionalRegex->match($condition)) {
                $left = (isset($conditionalRegex->matches[1])) ? $conditionalRegex->matches[1] : null;
                $operator = (isset($conditionalRegex->matches[4])) ? $conditionalRegex->matches[4] : null;
                $right = (isset($conditionalRegex->matches[5])) ? $conditionalRegex->matches[5] : null;

                array_push($conditions, array(
                    'left' => $this->numericFix($left),
                    'operator' => $this->numericFix($operator),
                    'right' => $this->numericFix($right)
                ));
            } else {
                throw new SyntaxError("Syntax Error in tag 'unless' - Valid syntax: unless [condition]", $this->getTagToken());
            }
        }

        $boolean = true;
        $results = array();
        foreach ($logicalOperators as $k => $logicalOperator) {
            $r = $this->interpretCondition($conditions[$k]['left'], $conditions[$k]['right'], $conditions[$k]['operator'], $context);
            if (in_array(strtolower($logicalOperator), ['and', '&&'])) {
                $boolean = $boolean && $this->isTruthy($r);
            } else {
                $results[] = $boolean;
                $boolean = $this->isTruthy($r);
            }
        }
        $results[] = $boolean;

        return in_array(true, $results) ? 'true' : 'false';
    }

    /**
     * @param $value
     * @return float|mixed
     */
    protected function numericFix($value)
    {
        if(!is_numeric($value)) {
            return $value;
        }

        if(($check = filter_var($value, FILTER_VALIDATE_INT)) !== false) {
            return $check;
        }

        return (float)$value;
    }

}
