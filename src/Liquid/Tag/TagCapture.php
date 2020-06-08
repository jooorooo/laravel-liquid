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
use Liquid\Exceptions\SyntaxError;
use Liquid\LiquidCompiler;
use Liquid\Regexp;
use Liquid\Tokens\TagToken;

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
     * @param TagToken $token
     * @param LiquidCompiler|null $compiler
     * @throws SyntaxError
     */
    public function __construct($markup, array &$tokens, $token, LiquidCompiler $compiler = null)
    {
        $syntaxRegexp = new Regexp('/[\'\"]?(\w+)[\'\"]?\s*/');

        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens, $token, $compiler);
        } else {
            throw new SyntaxError("Syntax Error in 'capture' - Valid syntax: capture [var]", $token);
        }
    }

    /**
     * Renders the block
     *
     * @param Context $context
     *
     * @return string
     * @throws SyntaxError
     */
    public function render(Context $context)
    {
        if(($protected_variables = config('liquid.protected_variables', [])) && is_array($protected_variables) && in_array($this->to, $protected_variables)) {
            throw new SyntaxError(sprintf('Variable "%s" is protected!', $this->to), $this->getTagToken());
        }

        $context->setToken($this->getTagToken());

        $context->registers['capture'][$this->to] = $this->to;
        $context->set($this->to, function() use($context) {
            return trim(parent::render($context));
        }, true);

        return '';
    }
}
