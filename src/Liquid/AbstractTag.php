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

use Liquid\Traits\TokenizeTrait;

/**
 * Base class for tags.
 */
abstract class AbstractTag
{
    use TokenizeTrait;

    /**
     * The markup for the tag
     *
     * @var string
     */
    protected $markup;

    /**
     * Filesystem object is used to store and load compiled files
     *
     * @var LiquidCompiler
     */
    protected $compiler;

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Constructor.
     *
     * @param string $markup
     * @param array $tokens
     * @param LiquidCompiler|null $compiler
     */
    public function __construct($markup, array &$tokens, LiquidCompiler $compiler = null)
    {
        $this->markup = $markup;
        $this->compiler = $compiler;
        $this->parse($tokens);
    }

    /**
     * Parse the given tokens.
     *
     * @param array $tokens
     */
    public function parse(array &$tokens)
    {
        // Do nothing by default
    }

    /**
     * Render the tag with the given context.
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        return '';
    }

    /**
     * Extracts tag attributes from a markup string.
     *
     * @param string $markup
     */
    protected function extractAttributes($markup)
    {
        $this->attributes = array();

        $attributeRegexp = new Regexp(LiquidCompiler::TAG_ATTRIBUTES);

        $matches = $attributeRegexp->scan($markup);

        foreach ($matches as $match) {
            array_set($this->attributes, $match[0], $match[1]);
//            $this->attributes[$match[0]] = $match[1];
        }
    }

    /**
     * Returns the name of the tag.
     *
     * @return string
     */
    protected function name()
    {
        return strtolower(get_class($this));
    }
}
