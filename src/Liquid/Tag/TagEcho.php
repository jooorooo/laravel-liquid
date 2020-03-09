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
use Liquid\Document;
use Liquid\LiquidCompiler;
use Liquid\Context;
use Liquid\LiquidException;
use Liquid\Regexp;

/**
 * Use to print in liquid tag
 *
 * Example:
 *
 *     {% echo "aas fdsfds" %}
 */
class TagEcho extends AbstractTag
{
    /**
     * @var string The name of the template
     */
    private $string;

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
        $regex = new Regexp('/(' . Constant::QuotedFragmentPartial . ')/');
        if ($regex->match($markup)) {
            $this->string = $regex->matches[1];
        } else {
            throw new LiquidException("Error in tag 'echo' - Valid syntax: echo '[variable]'");
        }

        parent::__construct($markup, $tokens, $compiler);
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     * @throws LiquidException
     * @throws \Throwable
     */
    public function render(Context $context)
    {
        return $context->get($this->string);
    }
}