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

use Illuminate\Filesystem\Filesystem;
use Liquid\AbstractBlock;
use Liquid\Context;
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
    private $to;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     *
     * @param Filesystem|null $files
     * @param null $compiled
     * @throws LiquidException
     */
    public function __construct($markup, array &$tokens, Filesystem $files = null, $compiled = null)
    {
        $syntaxRegexp = new Regexp('/(\w+)/');

        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens, $files, $compiled);
        } else {
            throw new LiquidException("Syntax Error in 'capture' - Valid syntax: capture [var] [value]");
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
        $output = parent::render($context);

        $context->set($this->to, $output);
        return '';
    }
}
