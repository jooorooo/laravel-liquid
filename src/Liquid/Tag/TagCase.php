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
use Liquid\Traits\DecisionTrait;
use ReflectionException;

/**
 * A switch statement
 *
 * Example:
 *
 *     {% case condition %}{% when foo %} foo {% else %} bar {% endcase %}
 */
class TagCase extends AbstractBlock
{
    use DecisionTrait;

    /**
     * Stack of nodelists
     *
     * @var array
     */
    public $nodelists;

    /**
     * The nodelist for the else (default) nodelist
     *
     * @var array
     */
    public $elseNodelist;

    /**
     * The left value to compare
     *
     * @var string
     */
    public $left;

    /**
     * The current right value to compare
     *
     * @var mixed
     */
    public $right;

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
        $this->nodelists = array();
        $this->elseNodelist = array();

        parent::__construct($markup, $tokens, $token, $compiler);

        $syntaxRegexp = new Regexp('/' . LiquidCompiler::QUOTED_FRAGMENT . '/');

        if ($syntaxRegexp->match($markup)) {
            $this->left = $syntaxRegexp->matches[0];
        } else {
            throw new SyntaxError("Syntax Error in tag 'case' - Valid syntax: case [condition]", $token); // harry
        }
    }

    /**
     * Pushes the last nodelist onto the stack
     */
    public function endTag()
    {
        $this->pushNodelist();
    }

    /**
     * Unknown tag handler
     *
     * @param TagToken $token
     * @param array $tokens
     *
     * @throws SyntaxError
     * @throws ReflectionException
     */
    public function unknownTag($token, array $tokens)
    {
        $whenSyntaxRegexp = new Regexp('/' . LiquidCompiler::QUOTED_FRAGMENT . '/');

        switch ($token->getTag()) {
            case 'when':
                // push the current nodelist onto the stack and prepare for a new one
                if ($whenSyntaxRegexp->match($token->getParameters())) {
                    $this->pushNodelist();
                    $this->right = $whenSyntaxRegexp->matches[0];
                    $this->nodelist = array();

                } else {
                    throw new SyntaxError("Syntax Error in tag 'case' - Valid when condition: when [condition]", $token); // harry
                }
                break;

            case 'else':
                // push the last nodelist onto the stack and prepare to receive the else nodes
                $this->pushNodelist();
                $this->right = null;
                $this->elseNodelist = &$this->nodelist;
                $this->nodelist = array();
                break;

            default:
                parent::unknownTag($token, $tokens);
        }
    }

    /**
     * Pushes the current right value and nodelist into the nodelist stack
     */
    public function pushNodelist()
    {
        if (!is_null($this->right)) {
            $this->nodelists[] = array($this->right, $this->nodelist);
        }
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {

        $context->setToken($this->getTagToken());

        $output = '';
        $runElseBlock = true;

        foreach ($this->nodelists as $data) {
            list($right, $nodelist) = $data;

            if ($this->equalVariables($this->left, $right, $context)) {
                $runElseBlock = false;

                $context->push();
                $output .= $this->renderAll($nodelist, $context);
                $context->pop();
            }
        }

        if ($runElseBlock) {
            $context->push();
            $output .= $this->renderAll($this->elseNodelist, $context);
            $context->pop();
        }

        return $output;
    }
}
