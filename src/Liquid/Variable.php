<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use Liquid\Traits\HelpersTrait;

/**
 * Implements a template variable.
 */
class Variable
{

    use HelpersTrait;

    /**
     * @var array The filters to execute on the variable
     */
    private $filters;

    /**
     * @var string The name of the variable
     */
    private $name;

    /**
     * @var string The markup of the variable
     */
    protected $markup;

    /**
     * @var LiquidCompiler $compiler
     */
    protected $compiler;

    /**
     * Constructor
     *
     * @param string $markup
     * @param LiquidCompiler $compiler
     */
    public function __construct($markup, LiquidCompiler $compiler)
    {
        $this->markup = $markup;
        $this->compiler = $compiler;

        $filters = $this->tokenizeMarkup($markup);

        $this->filters = array();
        foreach($filters AS $filter) {
            $this->filters[] = array($filter['name'], !empty($filter['arguments']) ? $filter['arguments'] : array());
        }

        if ($this->compiler->getAutoEscape()) {
            // if auto_escape is enabled, and
            // - there's no raw filter, and
            // - no escape filter
            // - no other standard html-adding filter
            // then
            // - add a mandatory escape filter

            $addEscapeFilter = true;

            foreach ($this->filters as $filter) {
                // with empty filters set we would just move along
                if (in_array($filter[0], array('escape', 'escape_once', 'raw', 'newline_to_br'))) {
                    // if we have any raw-like filter, stop
                    $addEscapeFilter = false;
                    break;
                }
            }

            if ($addEscapeFilter) {
                $this->filters[] = array('escape', array());
            }
        }
    }

    /**
     * Gets the variable name
     *
     * @return string The name of the variable
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets all Filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Renders the variable with the data in the context
     *
     * @param Context $context
     *
     * @return mixed|string
     * @throws LiquidException
     */
    public function render(Context $context)
    {
        $output = $context->get($this->name);

        foreach ($this->filters as $filter) {
            list($filtername, $filterArgKeys) = $filter;

            $filterArgValues = array();

            foreach ($filterArgKeys as $arg_key) {
                $filterArgValues[] = $context->get($arg_key);
            }

            $output = $context->invoke($filtername, $output, $filterArgValues);
        }

        if (is_float($output)) {
            if ($output == (int)$output) {
                return number_format($output, 1);
            }
        }

        return $output;
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
        $tokens = token_get_all("<?php $markup ?>");
        $last = count($tokens) - 1;
        $filters = [];
        $filter_num = 0;
        foreach ($tokens AS $index => $token) {
            if($index != 0 && $index != $last) {
                if (is_string($token) && $token === '|') {
                    if(!$finish_name) {
                        $finish_name = true;
                    } else {
                        $filter_num++;
                    }
                    continue;
                } elseif(!$finish_name && trim(is_array($token) ? $token[1] : $token)) {
                    $this->name .= is_array($token) ? $token[1] : $token;
                } elseif ($finish_name && is_array($token) && count($token) == 3) {
                    if (empty($filters[$filter_num]['name']) && $token[0] != T_WHITESPACE) {
                        $filters[$filter_num]['name'] = $token[1];
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

        return $filters;
    }
}
