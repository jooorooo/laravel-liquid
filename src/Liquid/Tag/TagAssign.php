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
use Liquid\Constant;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;
use Liquid\Context;
use Liquid\Traits\HelpersTrait;
use Liquid\Variable;

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

        $syntaxRegexp = new Regexp('/(' . Constant::VariableSignaturePartial . '+)\s*=\s*(' . Constant::QuotedFragmentPartial . ')\s*/ms');
        if($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            $this->from = $syntaxRegexp->matches[2];
        } else {
            throw new LiquidException("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
        }

        $this->filters = (new Variable($markup, $compiler))->getFilters();
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
        if(($protected_variables = config('liquid.protected_variables', [])) && is_array($protected_variables)) {
            if(in_array($this->to, $protected_variables)) {
                throw new LiquidException(sprintf('Variable "%s" is protected!', $this->to));
            }
        }

        $output = $context->get($this->from);

        foreach (array_merge($this->globalFilters, $this->filters) as $filter) {
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
