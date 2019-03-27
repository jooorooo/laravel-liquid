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
    private $filters = array();

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
        $this->compiler = $compiler;
        $this->markup = $markup;

        if(preg_match('/(' . Constant::QuotedFragmentPartial . ')(.*)/ms', $markup, $m)) {
            $this->name = $m[1];
        }

        if(preg_match('/' . Constant::FilterSeparatorPartial . '\s*(.*)/ms', $markup, $m)) {
            if(preg_match_all('/(?:\s+|' . Constant::QuotedFragmentPartial . '|' . Constant::ArgumentSeparator . ')+/ms', $m[1], $s)) {
                foreach($s[0] AS $f) {
                    $f = trim($f);
                    if(preg_match_all('/(?:' . Constant::FilterArgumentSeparator . '|' . Constant::ArgumentSeparator . ')\s*((?:\w+\s*\:\s*)?' . Constant::QuotedFragmentPartial . ')/ms', $f, $a, PREG_PATTERN_ORDER)) {
                        $this->filters[] = array(array_first(explode(Constant::FilterArgumentSeparator, $f)), $a[1]);
                    } else {
                        $this->filters[] = array($f, array());
                    }
                }
            }
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
}
