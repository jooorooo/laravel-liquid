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
use Liquid\Exceptions\SyntaxError;
use Liquid\LiquidCompiler;
use Liquid\Context;
use Liquid\LiquidException;
use Liquid\Regexp;
use Throwable;

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
     * @param $token
     * @param LiquidCompiler|null $compiler
     * @throws SyntaxError
     */
    public function __construct($markup, array &$tokens, $token, LiquidCompiler $compiler = null)
    {
        $regex = new Regexp('/(' . Constant::QuotedFragmentPartial . ')/');
        if ($regex->match($markup)) {
            $this->string = $regex->matches[1];
        } else {
            throw new SyntaxError("Error in tag 'echo' - Valid syntax: echo '[variable]'", $token);
        }

        parent::__construct($markup, $tokens, $token, $compiler);
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

        $context->setToken($this->getTagToken());

        return $context->get($this->string);
    }
}
