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

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Liquid\AbstractTag;
use Liquid\Document;
use Liquid\Context;
use Liquid\Exceptions\SyntaxError;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;
use Liquid\Tokens\TagToken;
use Liquid\Tokens\TextToken;
use Liquid\Tokens\VariableToken;
use Throwable;

/**
 * https://github.com/harrydeluxe/php-liquid/wiki/Template-Inheritance
 * Extends a template by another one.
 *
 * Example:
 *
 *     {% extends "base" %}
 */
class TagExtends extends AbstractTag
{
    /**
     * @var string The name of the template
     */
    private $templateName;

    /**
     * @var Document The Document that represents the included template
     */
    private $document;

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
        $regex = new Regexp('/("[^"]+"|\'[^\']+\')?/');
        if ($regex->match($markup) && !empty($regex->matches[1])) {
            $this->templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
        } else {
            throw new SyntaxError("Error in tag 'extends' - Valid syntax: extends '[template name]'", $token);
        }

        parent::__construct($markup, $tokens, $token, $compiler);
    }

    /**
     * @param array $tokens
     *
     * @return array
     */
    private function findBlocks(array $tokens)
    {
        if(($tagKey = array_search(TagBlock::class, $this->compiler->getTags())) === false) {
            $tagKey = 'block';
        }

        $blocks = array();
        $name = null;
        /** @var TagToken|TextToken|VariableToken $token */
        foreach($tokens AS $token) {
            if($token instanceof TagToken && $token->getTag() === $tagKey && preg_match('/(\w+)\s*(.*)/', $token->getParameters(), $m)) {
                $name = $m[1];
                $blocks[$name] = array();
            } elseif($token instanceof TagToken && $token->getTag() === 'end' . $tagKey) {
                $name = null;
            } elseif(!is_null($name)) {
                array_push($blocks[$name], $token);
            }
        }

        return $blocks;
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
        try {
            // read the source of the template and create a new sub document
            $source = $this->compiler->getTemplateSource($this->templateName);
        } catch (Throwable $e) {
            if(preg_match('/View \[(.*)\] not found/', $e->getMessage(), $m)) {
                throw new SyntaxError(sprintf('View [%s] not found', str_replace('.', '/', $m[1]) . '.liquid'), $this->getTagToken());
            }

            throw $e;
        }

        // tokens in this new document
        $maintokens = $this->tokenize($source);

        if(($tagKey = array_search(get_class($this), $this->compiler->getTags())) === false) {
            $tagKey = 'extends';
        }
        $eRegexp = new Regexp('/^' . LiquidCompiler::OPERATION_TAGS[0] . '\s*' . $tagKey . '\s+(.*)?' . LiquidCompiler::OPERATION_TAGS[1] . '$/');
        foreach ($maintokens as $maintoken) {
            if ($eRegexp->match($maintoken->getCode())) {
                $m = $eRegexp->matches[1];
                break;
            }
        }

        if (isset($m)) {
            $rest = array_merge($maintokens, $tokens);
        } else {
            $childtokens = $this->findBlocks($tokens);

            if(($tagKey = array_search(TagBlock::class, $this->compiler->getTags())) === false) {
                $tagKey = 'block';
            }

            $name = null;
            $rest = array();
            $block_open = false;
            /** @var TagToken|TextToken|VariableToken $maintoken */
            foreach($maintokens AS $maintoken) {
                if($maintoken instanceof TagToken && $maintoken->getTag() === $tagKey && preg_match('/(\w+)\s*(.*)/', $maintoken->getParameters(), $m)) {
                    if(!empty($childtokens[$m[1]])) {
                        $block_open = true;
                        array_push($rest, $maintoken);
                        array_map(function($item) use(&$rest) {
                            array_push($rest, $item);
                        }, $childtokens[$m[1]]);
                    }
                }
                if (!$block_open) {
                    array_push($rest, $maintoken);
                }
                if($maintoken instanceof TagToken && $maintoken->getTag() === 'end' . $tagKey && $block_open) {
                    $block_open = false;
                    array_push($rest, $maintoken);
                }
            }
        }

        $this->document = new Document(null, $rest, $this->getTagToken(), $this->compiler);
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

        $context->push();
        $result = $this->document->render($context);
        $context->pop();
        return $result;
    }
}
