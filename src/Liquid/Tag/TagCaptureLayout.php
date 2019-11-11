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
use Liquid\AbstractTag;
use Liquid\Context;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;

class TagCaptureLayout extends AbstractTag
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
        $syntaxRegexp = new Regexp('/[\'\"](\w+)[\'\"]\s*(append)?/');

        $this->to = $markup;
        parent::__construct($markup, $tokens, $compiler);
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

        $context->set($this->to, $output, true);

        return '';
    }
}
