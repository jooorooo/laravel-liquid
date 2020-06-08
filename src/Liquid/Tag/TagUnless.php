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
use Liquid\LiquidException;
use Liquid\Tokens\TagToken;
use Liquid\Traits\ParseBracket;
use ReflectionException;

/**
 * An if statement
 *
 * Example:
 *
 *     {% unless true %} YES {% else %} NO {% endunless %}
 *
 *     will return:
 *     NO
 */
class TagUnless extends AbstractBlock
{

    use ParseBracket;

    /**
     * Array holding the nodes to render for each logical block
     *
     * @var array
     */
    protected $nodelistHolders = array();

    /**
     * Array holding the block type, block markup (conditions) and block nodelist
     *
     * @var array
     */
    protected $blocks = array();

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param $token
     * @param LiquidCompiler|null $compiler
     */
    public function __construct($markup, array &$tokens, $token, LiquidCompiler $compiler = null)
    {
        if(trim($markup) === '') {
            throw new SyntaxError("Syntax Error in 'unless' - Valid syntax: unless [condition]", $token);
        }

        $this->nodelist = &$this->nodelistHolders[count($this->blocks)];

        array_push($this->blocks, array('unless', $markup, &$this->nodelist));

        parent::__construct($markup, $tokens, $token, $compiler);
    }

    /**
     * Handler for unknown tags, handle else tags
     *
     * @param TagToken $token
     * @param array $tokens
     * @throws ReflectionException
     * @throws SyntaxError
     */
    public function unknownTag($token, array $tokens)
    {
        if (in_array($token->getTag(), ['else'])) {
            // Update reference to nodelistHolder for this block
            $this->nodelist = &$this->nodelistHolders[count($this->blocks) + 1];
            $this->nodelistHolders[count($this->blocks) + 1] = array();

            array_push($this->blocks, array($token->getTag(), $token->getParameters(), &$this->nodelist));

        } else {
            parent::unknownTag($token, $tokens);
        }
    }

    /**
     * Render the tag
     *
     * @param Context $context
     *
     * @return string
     * @throws LiquidException
     * @throws SyntaxError
     */
    public function render(Context $context)
    {
        $context->push();

        $result = '';
        foreach ($this->blocks as $block) {
            if ($block[0] == 'else') {
                $result = $this->renderAll($block[2], $context);
                break;
            }

            if ($block[0] == 'unless') {
                if ($this->recursiveReplaceBracket($block[1], $context) === 'false') {
                    $result = $this->renderAll($block[2], $context);
                    break;
                }
            }
        }

        $context->pop();

        return $result;
    }
}
