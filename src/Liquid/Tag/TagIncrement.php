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
use Liquid\Context;
use Liquid\Exceptions\SyntaxError;
use Liquid\LiquidCompiler;
use Liquid\Regexp;
use Liquid\Tokens\TagToken;

/**
 * Used to increment a counter into a template
 *
 * Example:
 *
 *     {% increment value %}
 *
 * @author Viorel Dram
 */
class TagIncrement extends AbstractTag
{
    /**
     * Name of the variable to increment
     *
     * @var string
     */
    private $toIncrement;

    /**
     * Constructor
     *
     * @param string $markup
     *
     * @param array|null $tokens
     * @param TagToken $token
     * @param LiquidCompiler|null $compiler
     * @throws SyntaxError
     */
    public function __construct($markup, array &$tokens, $token, LiquidCompiler $compiler = null)
    {
        $syntax = new Regexp('/(' . $compiler::VARIABLE_NAME . ')/');

        if ($syntax->match($markup)) {
            $this->toIncrement = $syntax->matches[0];
        } else {
            throw new SyntaxError("Syntax Error in 'increment' - Valid syntax: increment [var]", $token);
        }

        parent::__construct(null, $tokens, $token, $compiler);
    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return string|void
     */
    public function render(Context $context)
    {
        // If the value is not set in the environment check to see if it
        // exists in the context, and if not set it to -1
        if (!isset($context->registers['increments_decrements'][$this->toIncrement])) {
            // we already have a value in the context
            $context->registers['increments_decrements'][$this->toIncrement] = -1;
        }

        // Increment the value
        $context->registers['increments_decrements'][$this->toIncrement]++;

        return $context->registers['increments_decrements'][$this->toIncrement];
    }
}
