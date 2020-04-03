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

/**
 * Captures the output inside a block and assigns it to a variable
 *
 * Example:
 *
 *     {% capture foo %} bar {% endcapture %}
 */
class TagCapture extends AbstractBlock
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    protected $to;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     *
     * @param LiquidCompiler|null $compiler
     * @throws LiquidException
     */
    public function __construct($markup, array &$tokens, LiquidCompiler $compiler = null)
    {
        $syntaxRegexp = new Regexp('/[\'\"](\w+)[\'\"]\s*/');

        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens, $compiler);
        } else {
            throw new LiquidException("Syntax Error in 'capture' - Valid syntax: capture [var]");
        }
    }

    /**
     * Renders the block
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        if(($protected_variables = config('liquid.protected_variables', [])) && is_array($protected_variables)) {
            if(in_array($this->to, $protected_variables) && !$this->callFormTagLayout()) {
                throw new LiquidException(sprintf('Variable "%s" is protected!', $this->to));
            }
        }

        $context->set($this->to, function() use($context) {
            return trim(parent::render($context));
        }, true);

        return '';
    }

    /**
     * @return bool
     */
    protected function callFormTagLayout()
    {
        return !empty(array_filter(array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, 7), function($r) {
            return ($r['class'] ?? null) == TagLayout::class;
        }));
    }
}
