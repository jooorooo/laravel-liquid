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
use Liquid\Exceptions\SyntaxError;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;
use Liquid\Tokens\TagToken;
use Throwable;

/**
 * https://github.com/harrydeluxe/php-liquid/wiki/Template-Inheritance
 * Layout a template by another one.
 *
 * Example:
 *
 *     {% layout "base" %}
 */
class TagLayout extends AbstractTag
{
    /**
     * @var string The name of the template
     */
    private $layoutPath;

    /**
     * @var Document The Document that represents the included template
     */
    private $document;

    /**
     * @var Document The Document that represents the included template
     */
    private $document2;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     *
     * @param TagToken $token
     * @param LiquidCompiler|null $compiler
     * @throws LiquidException
     */
    public function __construct($markup, array &$tokens, $token, LiquidCompiler $compiler = null)
    {
        $regex = new Regexp('/("[^"]+"|\'[^\']+\')?/');
        if ($regex->match($markup) && !empty($regex->matches[1])) {
            $this->layoutPath = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
        } else {
            throw new SyntaxError("Error in tag 'layout' - Valid syntax: layout '[template path]'", $token);
        }

        parent::__construct($markup, $tokens, $token, $compiler);
    }

    /**
     * Parses the tokens
     *
     * @param array $tokens
     * @throws SyntaxError
     * @throws Throwable
     */
    public function parse(array &$tokens)
    {
        if($this->layoutPath == 'none') {
            $this->document = new Document(null, $tokens, $this->getTagToken(), $this->compiler);
        } else {
            try {
                // read the source of the template and create a new sub document
                $source = $this->compiler->getTemplateSource($this->layoutPath . '.theme');
            } catch (Throwable $e) {
                if(preg_match('/View \[(.*)\] not found/', $e->getMessage(), $m)) {
                    throw new SyntaxError(sprintf('View [%s] not found', str_replace('.', '/', $m[1]) . '.liquid'), $this->getTagToken());
                }

                throw $e;
            }

            // tokens in this new document
            $maintokens = $this->tokenize($source);

            $this->document = new Document(null, $tokens, $this->getTagToken(), $this->compiler);
            $this->document2 = new Document(null, $maintokens, $this->getTagToken(), $this->compiler);

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
        $context->push();

        if($this->document2) {
            $context->set('content_for_layout', $this->document->render($context));

            $result = $this->document2->render($context);
        } else {
            $result = $this->document->render($context);
        }
        $context->pop();
        return $result;
    }
}
