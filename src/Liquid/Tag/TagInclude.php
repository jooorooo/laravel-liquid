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
use Liquid\Document;
use Liquid\Context;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;

/**
 * Includes another, partial, template
 *
 * Example:
 *
 *     {% include 'foo' %}
 *
 *     Will include the template called 'foo'
 *
 *     {% include 'foo' with 'bar' %}
 *
 *     Will include the template called 'foo', with a variable called foo that will have the value of 'bar'
 *
 *     {% include 'foo' for 'bar' %}
 *
 *     Will loop over all the values of bar, including the template foo, passing a variable called foo
 *     with each value of bar
 */
class TagInclude extends AbstractTag
{
    /**
     * @var string The name of the template
     */
    private $templateName;

    /**
     * @var bool True if the variable is a collection
     */
    private $collection;

    /**
     * @var mixed The value to pass to the child template as the template name
     */
    private $variable;

    /**
     * @var Document The Document that represents the included template
     */
    private $document;

    /**
     * @var bool check if self included
     */
    private $self_include = false;

    /**
     * @var bool check if is dinamic
     */
    private $dinamic = false;

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
        $regex = new Regexp('/("[^"]+"|\'[^\']+\')(\s+(with|for)\s+(' . $compiler::QUOTED_FRAGMENT . '+))?/');
        if ($regex->match($markup)) {
            $this->templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);

            if (isset($regex->matches[1])) {
                $this->collection = (isset($regex->matches[3])) ? ($regex->matches[3] == "for") : null;
                $this->variable = (isset($regex->matches[4])) ? $regex->matches[4] : null;
            }

            $this->extractAttributes($markup);
        } else {
            throw new LiquidException("Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]");
        }

        parent::__construct($markup, $tokens, $compiler);
    }

    /**
     * Parses the tokens
     *
     * @param array $tokens
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function parse(array &$tokens)
    {
        // read the source of the template and create a new sub document
        $source = $this->compiler->getTemplateSource('snippets.' . $this->templateName);

        if(($tagName = array_search(get_class($this), $this->compiler->getTags())) === false) {
            $tagName = 'render';
        }

        $regex = new Regexp(sprintf('/%s\s*%s\s*%s(%s)%s/imUs', Constant::TagStartPartial, $tagName, '[\'\"]', $this->templateName, '[\'\"]'));
        $this->self_include = (bool)$regex->match($source);

        if(!$this->self_include) {
            $templateTokens = $this->tokenize($source);
            $this->document = new Document(null, $templateTokens, $this->compiler);
        }
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     * @throws LiquidException
     * @throws \Throwable
     */
    public function render(Context $context)
    {
        if($this->self_include) {
            return $this->_renderOutline($context);
        } else {
            return $this->_renderInline($context);
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
    protected function _renderInline(Context $context)
    {
        $result = '';
        $variable = $context->get($this->variable);

        $context->push();

        foreach ($this->attributes as $key => $value) {
            $context->set($key, $context->get($value));
        }

        if ($this->collection) {
            if(is_array($variable)) {
                foreach ($variable as $item) {
                    $context->set($this->templateName, $item);
                    $result .= $this->document->render($context);
                }
            }

        } else {
            if (!is_null($this->variable)) {
                $context->set($this->templateName, $variable);
            }

            $result .= $this->document->render($context);
        }

        $context->pop();

        return $result;
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     * @throws LiquidException
     * @throws \Throwable
     */
    protected function _renderOutline(Context $context)
    {
        $result = '';
        $variable = $context->get($this->variable);

        $context->push();

        foreach ($this->attributes as $key => $value) {
            $context->set($key, $context->get($value));
        }

        if ($this->collection) {
            if(is_array($variable)) {
                $templateTokens = $this->tokenize($this->compiler->getTemplateSource('snippets.' . $this->templateName));
                $document = new Document(null, $templateTokens, $this->compiler);

                foreach ($variable as $item) {
                    $context->set($this->templateName, $item);
                    $result .= $document->render($context);
                }
            }

        } else {
            if (!is_null($this->variable)) {
                $context->set($this->templateName, $variable);
            }

            $templateTokens = $this->tokenize($this->compiler->getTemplateSource('snippets.' . $this->templateName));
            $document = new Document(null, $templateTokens, $this->compiler);
            $result .= $document->render($context);
        }

        $context->pop();

        return $result;
    }
}
