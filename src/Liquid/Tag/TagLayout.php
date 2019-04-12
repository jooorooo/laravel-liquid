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
use Liquid\Document;
use Liquid\Context;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;

/**
 * https://github.com/harrydeluxe/php-liquid/wiki/Template-Inheritance
 * Layout a template by another one.
 *
 * Example:
 *
 *     {% layout "layout2" %}
 *     {% layout "none" %}
 */
class TagLayout extends AbstractTag
{
    /**
     * @var string The name of the template
     */
    protected $path;

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
        $regex = new Regexp('/("[^"]+"|\'[^\']+\')?/');

        if ($regex->match($markup)) {
            $this->path = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
        } else {
            throw new LiquidException("Error in tag 'layout' - Valid syntax: layout '[layout template dir]'");
        }
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     * @throws LiquidException
     */
    public function render(Context $context)
    {
        return '';
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }
}
