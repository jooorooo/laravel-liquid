<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use Liquid\Exceptions\SyntaxError;
use Liquid\Tag\TagBlock;
use Liquid\Tag\TagLayout;

/**
 * Base class for blocks.
 */
class AbstractBlock extends AbstractTag
{
    /**
     * @var AbstractTag[]
     */
    protected $nodelist = array();

    /**
     * @return array
     */
    public function getNodelist()
    {
        return $this->nodelist;
    }

    /**
     * Parses the given tokens
     *
     * @param array $tokens
     *
     * @return void
     * @throws LiquidException
     * @throws \ReflectionException
     */
    public function parse(array &$tokens)
    {
        $startRegexp = new Regexp('/^' . LiquidCompiler::OPERATION_TAGS[0] . '(-)?/');
        $tagRegexp = new Regexp('/^' . LiquidCompiler::OPERATION_TAGS[0] . '(-)?\s*(\w+)\s*(.*)?' . LiquidCompiler::OPERATION_TAGS[1] . '$/s');
        $variableStartRegexp = new Regexp('/^' . LiquidCompiler::VARIABLE_TAG[0] . '/');

        $this->nodelist = array();

        if (!is_array($tokens)) {
            return;
        }

        $tags = $this->compiler->getTags();

        while (count($tokens)) {
            $token = array_shift($tokens);

            if ($startRegexp->match($token)) {
                if ($tagRegexp->match($token)) {
                    // If we found the proper block delimiter just end parsing here and let the outer block proceed
                    if ($tagRegexp->matches[2] == $this->blockDelimiter()) {
                        $this->endTag();
                        return;
                    }

                    $tagName = null;
                    if (array_key_exists($tagRegexp->matches[2], $tags)) {
                        $tagName = $tags[$tagRegexp->matches[2]];
                    }

                    $filters = [];
                    if($tagRegexp->matches[1] == '-') {
                        $filters[] = 'lstrip';
                    }
                    if(substr($tagRegexp->matches[3], -1) == '-') {
                        $filters[] = 'rstrip';
                        $tagRegexp->matches[3] = substr($tagRegexp->matches[3], 0, -1);
                    }

                    if ($tagName !== null) {
                        /** @var AbstractTag $node */
                        $node = new $tagName($tagRegexp->matches[3], $tokens, $this->compiler);
                        if($filters) {
                            $node->setFilters($filters);
                        }
                        $this->nodelist[] = $node;
                        if (in_array($tagRegexp->matches[2], ['extends', 'layout'])) {
                            return;
                        }

                    } else {
                        $this->unknownTag($tagRegexp->matches[2], $tagRegexp->matches[3], $tokens, $this->compiler->getTextLine($tagRegexp->matches[0]));
                    }
                } else {
                    throw new LiquidException("Tag $token was not properly terminated"); // harry
                }

            } elseif ($variableStartRegexp->match($token)) {
                $this->nodelist[] = $this->createVariable($token);

            } elseif ($token != '') {
                $this->nodelist[] = $token;
            }
        }

        $this->assertMissingDelimitation();
    }

    /**
     * Render the block.
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        return $this->renderAll($this->nodelist, $context);
    }

    /**
     * Renders all the given nodelist's nodes
     *
     * @param array $list
     * @param Context $context
     *
     * @return string
     */
    protected function renderAll(array $list, Context $context)
    {
        $result = '';

        foreach ($list as $token) {
            if(is_object($token) && method_exists($token, 'render')) {
                $renderResult = $token->render($context);
            } else {
                $renderResult = $token;
            }

            if(is_scalar($renderResult)) {
                $result .= $renderResult;
            } elseif(is_object($renderResult) && method_exists($renderResult, '__toString')) {
                $result .= $renderResult->__toString();
            }

            if (isset($context->registers['break'])) {
                break;
            }
            if (isset($context->registers['continue'])) {
                break;
            }
        }

        return $result;
    }

    /**
     * An action to execute when the end tag is reached
     */
    protected function endTag()
    {
        // Do nothing by default
    }

    /**
     * Handler for unknown tags
     *
     * @param string $tag
     * @param string $params
     * @param array $tokens
     *
     * @throws LiquidException
     * @throws \ReflectionException
     */
    protected function unknownTag($tag, $params, array $tokens, $line = 0)
    {
        switch ($tag) {
            case 'else':
                throw new LiquidException($this->blockName() . " does not expect else tag");
            case 'end':
                throw new LiquidException("'end' is not a valid delimiter for " . $this->blockName() . " tags. Use " . $this->blockDelimiter());
            default:
                //@todo must be make better
                $e = new SyntaxError(sprintf('Unknown "%s" tag.', $tag), $line, $this->compiler);
                $e->addSuggestions($tag, array_keys($this->compiler->getTags()));
                throw $e;
                //throw new LiquidException("Unknown tag $tag");
        }
    }

    /**
     * This method is called at the end of parsing, and will through an error unless
     * this method is subclassed, like it is for Document
     *
     * @return void
     * @throws LiquidException
     * @throws \ReflectionException
     */
    protected function assertMissingDelimitation()
    {
        throw new LiquidException($this->blockName() . " tag was never closed");
    }

    /**
     * Returns the string that delimits the end of the block
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function blockDelimiter()
    {
        return "end" . $this->blockName();
    }

    /**
     * Returns the name of the block
     *
     * @return string
     * @throws \ReflectionException
     */
    private function blockName()
    {
        $reflection = new \ReflectionClass($this);
        return str_replace('tag', '', strtolower($reflection->getShortName()));
    }

    /**
     * Create a variable for the given token
     *
     * @param string $token
     *
     * @throws \Liquid\LiquidException
     * @return Variable
     */
    private function createVariable($token)
    {
        $variableRegexp = new Regexp('/^' . LiquidCompiler::VARIABLE_TAG[0] . '(.*)' . LiquidCompiler::VARIABLE_TAG[1] . '$/');
        if ($variableRegexp->match($token)) {
            return $this->compileVariable($variableRegexp->matches[1]);
        }

        throw new LiquidException("Variable $token was not properly terminated");
    }

    /**
     * Create a variable for the given token
     *
     * @param string $token
     *
     * @throws \Liquid\LiquidException
     * @return Variable
     */
    private function compileVariable($variable)
    {
        $filters = [];
        if(substr($variable, 0, 1) == '-') {
            $filters[] = 'lstrip';
            $variable = substr($variable, 1);
        }
        if(substr($variable, -1) == '-') {
            $filters[] = 'rstrip';
            $variable = substr($variable, 0, -1);
        }

        $variableObject = new Variable(trim($variable), $this->compiler);
        $variableObject->preSetFilters($filters);
        return $variableObject;
    }
}