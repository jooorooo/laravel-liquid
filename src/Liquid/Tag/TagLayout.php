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
     * @var variable name to assign content for layout
     */
    private $assign_to = 'content_for_layout';

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
        //$regex = new Regexp('/("[^"]+"|\'[^\']+\')?/');
        $regex = new Regexp('/("[^"]+"|\'[^\']+\')(\s+(' . $compiler::QUOTED_FRAGMENT . '+))?/');
        if ($regex->match($markup)) {
            $this->layoutPath = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
            if(!empty($regex->matches[3])) {
                $this->assign_to = substr($regex->matches[3], 1, strlen($regex->matches[3]) - 2);
            }

            $compiler->setLayoutVariableName($this->assign_to);
        } else {
            throw new LiquidException("Error in tag 'layout' - Valid syntax: layout '[template path]'");
        }

        parent::__construct($markup, $tokens, $compiler);
    }

    /**
     * Parses the tokens
     *
     * @param array $tokens
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function parse(array &$tokens)
    {
        if($this->layoutPath == 'none') {
            $rest = $tokens;
        } else {
            // read the source of the template and create a new sub document
            $source = $this->compiler->getTemplateSource($this->layoutPath . '.theme');

            // tokens in this new document
            $maintokens = $this->tokenize($source);

            $rest = array_merge([
                '{% capture "' . $this->assign_to . '" %}'
            ], $tokens, [
                '{% endcapture %}'
            ], $maintokens);
        }

        $this->document = new Document(null, $rest, $this->compiler);
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
        $result = $this->document->render($context);
        $context->pop();
        return $result;
    }
}
