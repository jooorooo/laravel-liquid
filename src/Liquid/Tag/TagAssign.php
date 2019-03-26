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

use Liquid\AbstractTag;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;
use Liquid\Context;
use Liquid\Traits\HelpersTrait;

/**
 * Performs an assignment of one variable to another
 *
 * Example:
 *
 *     {% assign var = var %}
 *     {% assign var = "hello" | upcase %}
 */
class TagAssign extends AbstractTag
{

    use HelpersTrait;

    /**
     * @var string The variable to assign from
     */
    private $from;

    /**
     * @var string The variable to assign to
     */
    private $to;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * Constructor
     *
     * @param string $markup
     *
     * @param array|null $tokens
     * @param LiquidCompiler|null $compiler
     * @throws LiquidException
     */
    public function __construct($markup, array &$tokens = null, LiquidCompiler $compiler = null)
    {
        parent::__construct(null, $tokens, $compiler);

        list($to, $from, $filters) = $this->tokenizeMarkup($markup);

        if ($from && $to) {
            $this->to = $to;
            $this->from = $from;
        } else {
            throw new LiquidException("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
        }

        if ($filters) {
            foreach ($filters as $filter) {
                $matches = $this->arrayFlatten($filter['arguments']);

                array_push($this->filters, array($filter['name'], $matches));
            }
        }
    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return string|void
     * @throws LiquidException
     */
    public function render(Context $context)
    {
        $output = $context->get($this->from);

        foreach ($this->filters as $filter) {
            list($filtername, $filterArgKeys) = $filter;

            $filterArgValues = array();

            foreach ($filterArgKeys as $arg_key) {
                $filterArgValues[] = $context->get($arg_key);
            }

            $output = $context->invoke($filtername, $output, $filterArgValues);
        }

        $context->set($this->to, $output, true);
    }

    /**
     * Tokenize markup text
     *
     * @param string $markup
     * @return array
     */
    protected function tokenizeMarkup($markup)
    {
        $finish_name = false;
        $finish_sub_name = false;
        $tokens = token_get_all("<?php $markup ?>");
        $last = count($tokens) - 1;
        $filters = [];
        $filter_num = 0;
        $name = '';
        $sub_name = '';
        foreach ($tokens AS $index => $token) {
            if($index != 0 && $index != $last) {
                if (is_string($token) && $token === '=') {
                    $finish_name = true;
                    continue;
                }
                if (is_string($token) && $token === '|') {
                    if($finish_name) {
                        $finish_sub_name = true;
                    } else {
                        $filter_num++;
                    }
                    continue;
                }
                if((!$finish_name || !$finish_sub_name) && trim(is_array($token) ? $token[1] : $token)) {
                    if($finish_name) {
                        $sub_name .= is_array($token) ? $token[1] : $token;
                    } else {
                        $name .= is_array($token) ? $token[1] : $token;
                    }
                } elseif ($finish_name && $finish_sub_name && is_array($token) && count($token) == 3) {
                    if (empty($filters[$filter_num]['name']) && $token[0] != T_WHITESPACE) {
                        $filters[$filter_num] = [
                            'name' => $token[1], 'arguments' => []
                        ];
                        continue;
                    } elseif (!empty($filters[$filter_num]['name']) && $token[0] != T_WHITESPACE) {
                        if ($token[0] == T_CONSTANT_ENCAPSED_STRING) {
                            $token = substr($token[1], 1, -1);
                        } else {
                            $token = $token[1];
                        }
                        if(is_numeric($token)) {
                            $filters[$filter_num]['arguments'][] = $token;
                        } else {
                            $filters[$filter_num]['arguments'][] = sprintf('"%s"', $token);
                        }
                    }
                }
            }
        }

        return [$name, $sub_name, $filters];
    }
}
