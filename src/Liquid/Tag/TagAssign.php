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

        $syntaxRegexp = new Regexp('/(\w+)\s*=\s*(' . LiquidCompiler::QUOTED_FRAGMENT . '+)/');

        $filterSeperatorRegexp = new Regexp('/' . LiquidCompiler::FILTER_SEPARATOR . '\s*(.*)/');
        $filterSplitRegexp = new Regexp('/' . LiquidCompiler::FILTER_SEPARATOR . '/');
        $filterNameRegexp = new Regexp('/\s*(\w+)/');
        $filterArgumentRegexp = new Regexp('/(?:' . LiquidCompiler::FILTER_ARGUMENT_SEPARATOR . '|' . LiquidCompiler::ARGUMENT_SEPARATOR . ')\s*(' . LiquidCompiler::QUOTED_FRAGMENT . ')/');

        if ($filterSeperatorRegexp->match($markup)) {
            $filters = $filterSplitRegexp->split($filterSeperatorRegexp->matches[1]);

            foreach ($filters as $filter) {
                $filterNameRegexp->match($filter);
                $filtername = $filterNameRegexp->matches[1];

                $filterArgumentRegexp->matchAll($filter);
                $matches = $this->arrayFlatten($filterArgumentRegexp->matches[1]);

                array_push($this->filters, array($filtername, $matches));
            }
        }
        
        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            $this->from = $syntaxRegexp->matches[2];
        } else {
            throw new LiquidException("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
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
}
