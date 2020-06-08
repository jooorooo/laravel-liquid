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

/**
 * Used to decrement a counter into a template
 *
 * Example:
 *
 *     {% decrement value %}
 *
 * @author Viorel Dram
 */
class TagDecrement extends AbstractTag
{
    /**
     * Name of the variable to decrement
     *
     * @var int
     */
    private $toDecrement;

    /**
     * Constructor
     *
     * @param string $markup
     *
     * @param array|null $tokens
     * @param $token
     * @param LiquidCompiler|null $compiler
     * @throws SyntaxError
     */
    public function __construct($markup, array &$tokens, $token, LiquidCompiler $compiler = null)
    {
        parent::__construct(null, $tokens, $token, $compiler);

        $syntax = new Regexp('/(' . LiquidCompiler::VARIABLE_NAME . ')/');

        if ($syntax->match($markup)) {
            $this->toDecrement = $syntax->matches[0];
        } else {
            throw new SyntaxError("Syntax Error in 'decrement' - Valid syntax: decrement [var]", $token);
        }
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
        // if the value is not set in the environment check to see if it
        // exists in the context, and if not set it to 0
        if (!isset($context->registers['increments_decrements'][$this->toDecrement])) {
            // we already have a value in the context
            $context->registers['increments_decrements'][$this->toDecrement] = 0;
        }

        // decrement the environment value
        $context->registers['increments_decrements'][$this->toDecrement]--;

        return $context->registers['increments_decrements'][$this->toDecrement];
    }
}
