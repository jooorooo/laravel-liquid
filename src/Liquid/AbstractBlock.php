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

use Illuminate\Support\Str;
use Liquid\Exceptions\SyntaxError;
use Liquid\Tag\TagBlock;
use Liquid\Tag\TagLayout;
use Liquid\Tokens\TagToken;
use Liquid\Tokens\TextToken;
use Liquid\Tokens\VariableToken;
use ReflectionException;

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
     * @throws ReflectionException
     */
    public function parse(array &$tokens)
    {
        $this->nodelist = array();

        if (!is_array($tokens)) {
            return;
        }

        $tags = $this->compiler->getTags();

        while (count($tokens)) {
            $token = array_shift($tokens);

            if($token instanceof TextToken) {
                if($token->getCode() != '') {
                    $this->nodelist[] = $token->getCode();
                }
            } elseif($token instanceof VariableToken) {
                $variableObject = new Variable($token->getVariable(), $this->compiler);
                if($filters = $token->getFilters()) {
                    $variableObject->preSetFilters($filters);
                }

                $this->nodelist[] = $variableObject;
            } elseif($token instanceof TagToken) {
                if($token->getTag() == $this->blockDelimiter()) {
                    $this->endTag();
                    return;
                }

                if (!array_key_exists($token->getTag(), $tags)) {
                    $this->unknownTag($token, $tokens);
                    continue;
                }

                $tagName = $tags[$token->getTag()];

                /** @var AbstractTag $node */
                $node = new $tagName(trim(Str::substr($token->getToken(), Str::length($token->getTag()))), $tokens, $this->compiler);
                if($filters = $token->getFilters()) {
                    $node->setFilters($filters);
                }

                $this->nodelist[] = $node;
                if (in_array($token->getTag(), ['extends', 'layout'])) {
                    return;
                }
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
     * @param TagToken $token
     * @param array $tokens
     *
     * @throws LiquidException
     * @throws SyntaxError
     * @throws ReflectionException
     */
    protected function unknownTag($token, array $tokens)
    {
        switch ($token->getTag()) {
            case 'else':
                throw new LiquidException($this->blockName() . " does not expect else tag");
            case 'end':
                throw new LiquidException("'end' is not a valid delimiter for " . $this->blockName() . " tags. Use " . $this->blockDelimiter());
            default:
                //@todo must be make better
                $e = new SyntaxError(sprintf('Unknown "%s" tag.', $token->getTag()), $token, $this->compiler);
                $e->addSuggestions($token->getTag(), array_keys($this->compiler->getTags()));
                throw $e;
        }
    }

    /**
     * This method is called at the end of parsing, and will through an error unless
     * this method is subclassed, like it is for Document
     *
     * @return void
     * @throws LiquidException
     * @throws ReflectionException
     */
    protected function assertMissingDelimitation()
    {
        throw new LiquidException($this->blockName() . " tag was never closed");
    }

    /**
     * Returns the string that delimits the end of the block
     *
     * @return string
     * @throws ReflectionException
     */
    protected function blockDelimiter()
    {
        return "end" . $this->blockName();
    }

    /**
     * Returns the name of the block
     *
     * @return string
     * @throws ReflectionException
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
