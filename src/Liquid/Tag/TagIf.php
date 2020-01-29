<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\AbstractBlock;
use Liquid\Context;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;
use Liquid\Traits\DecisionTrait;
use Liquid\Traits\HelpersTrait;

/**
 * An if statement
 *
 * Example:
 *
 *     {% if true %} YES {% else %} NO {% endif %}
 *
 *     will return:
 *     YES
 *
 * 0 is truthy
 *
 *     {% if 0 %} YES {% else %} NO {% endif %}
 *
 *     will return:
 *     YES
 */
class TagIf extends AbstractBlock
{

    use DecisionTrait, HelpersTrait;

    /**
     * Array holding the nodes to render for each logical block
     *
     * @var array
     */
    private $nodelistHolders = array();

    /**
     * Array holding the block type, block markup (conditions) and block nodelist
     *
     * @var array
     */
    protected $blocks = array();

    /**
     * @var array
     */
    protected $conditional_operators = [
        '==', '!=', '>=', '<=',  '>', '<', 'contains'
    ];

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param LiquidCompiler|null $compiler
     */
    public function __construct($markup, array &$tokens, LiquidCompiler $compiler = null)
    {
        $this->nodelist = &$this->nodelistHolders[count($this->blocks)];

        array_push($this->blocks, array('if', $markup, &$this->nodelist));

        parent::__construct($markup, $tokens, $compiler);
    }

    /**
     * Handler for unknown tags, handle else tags
     *
     * @param string $tag
     * @param array $params
     * @param array $tokens
     * @param int $line
     * @throws LiquidException
     * @throws \ReflectionException
     */
    public function unknownTag($tag, $params, array $tokens, $line = 0)
    {
        if ($tag == 'else' || $tag == 'elsif') {
            // Update reference to nodelistHolder for this block
            $this->nodelist = &$this->nodelistHolders[count($this->blocks) + 1];
            $this->nodelistHolders[count($this->blocks) + 1] = array();

            array_push($this->blocks, array($tag, $params, &$this->nodelist));

        } else {
            parent::unknownTag($tag, $params, $tokens, $line);
        }
    }

    /**
     * @param $string
     * @param Context $context
     * @return string
     * @throws LiquidException
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
     * @throws LiquidException
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
                    'left' => $left,
                    'operator' => $operator,
                    'right' => $right
                ));
            } else {
                throw new LiquidException("Syntax Error in tag 'if' - Valid syntax: if [condition]");
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
     * Render the tag
     *
     * @param Context $context
     *
     * @throws \Liquid\LiquidException
     * @return string
     */
    public function render(Context $context)
    {
        $context->push();

        $result = '';
        foreach ($this->blocks as $block) {
            if ($block[0] == 'else') {
                $result = $this->renderAll($block[2], $context);

                break;
            }

            if ($block[0] == 'if' || $block[0] == 'elsif') {
                if ($this->recursiveReplaceBracket($block[1], $context) === 'true') {
                    $result = $this->renderAll($block[2], $context);
                    break;
                }
            }
        }

        $context->pop();

        return $result;
    }
}