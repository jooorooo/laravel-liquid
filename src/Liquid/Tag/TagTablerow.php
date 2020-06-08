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
use Liquid\Regexp;

/**
 * Quickly create a table from a collection
 */
class TagTablerow extends AbstractBlock
{
    /**
     * The variable name of the table tag
     *
     * @var string
     */
    public $variableName;

    /**
     * The collection name of the table tags
     *
     * @var string
     */
    public $collectionName;

    /**
     * Additional attributes
     *
     * @var array
     */
    public $attributes;

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
        parent::__construct($markup, $tokens, $token, $compiler);

        $syntax = new Regexp('/(\w+)\s+in\s+(' . LiquidCompiler::VARIABLE_NAME . ')/');

        if ($syntax->match($markup)) {
            $this->variableName = $syntax->matches[1];
            $this->collectionName = $syntax->matches[2];

            $this->extractAttributes($markup);
        } else {
            throw new SyntaxError("Syntax Error in 'table_row loop' - Valid syntax: tablerow [item] in [collection] cols:3", $token);
        }
    }

    /**
     * Renders the current node
     *
     * @param Context $context
     *
     * @return string
     * @throws LiquidException
     */
    public function render(Context $context)
    {
        $collection = $context->get($this->collectionName);

        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        if (!is_array($collection)) {
            return '';
        }

        // discard keys
        $collection = array_values($collection);

        if (isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
            $limit = $context->get($this->attributes['limit']);
            $offset = $context->get($this->attributes['offset']);
            $collection = array_slice($collection, $offset, $limit);
        }

        $length = count($collection);

        $cols = isset($this->attributes['cols']) ? $context->get($this->attributes['cols']) : PHP_INT_MAX;

        $context->push();

        $result = '';

        $rows = array_chunk($collection, $cols);
        $rows = array_map(function($columns) use($cols) {
            $columns = array_replace(array_fill(0, $cols, null), $columns);
            return $columns;
        }, $rows);

        $index = 0;
        foreach($rows AS $rowIndex => $columns) {
            $result .= "<tr class=\"row" . ($rowIndex + 1) . "\">\n";
            $break = $continue = false;
            foreach($columns AS $colIndex => $col) {
                $context->set($this->variableName, null);
                $context->set('tablerowloop', null);

                $result .= "<td class=\"col" . ($colIndex + 1) . "\">\n";

                if($index < $length) {
                    $context->set($this->variableName, $col);
                    $context->set('tablerowloop', array(
                        'key' => $index,
                        'name' => $this->collectionName,
                        'length' => $length,
                        'index' => $index + 1,
                        'index0' => $index,
                        'rindex' => $length - $index,
                        'rindex0' => $length - $index - 1,
                        'first' => (int)($index == 0),
                        'last' => (int)($index == $length - 1)
                    ));

                    $break = $break ? $break : isset($context->registers['break']);
                    $continue = isset($context->registers['continue']);

                    if(!$continue || !$break) {
                        $result .= trim($this->renderAll($this->nodelist, $context));
                    }

                    if(isset($context->registers['continue'])) {
                        unset($context->registers['continue']);
                    }

                    if(isset($context->registers['break'])) {
                        unset($context->registers['break']);
                    }
                }

                $result .= "</td>\n";

                $index++;
            }
            $result .= "</tr>\n";

            if($break) {
                unset($context->registers['break']);
                break;
            }
        }

        if(isset($context->registers['break'])) {
            unset($context->registers['break']);
        }

        if(isset($context->registers['continue'])) {
            unset($context->registers['continue']);
        }

        $context->pop();

        $result .= "</tr>\n";

        return $result;
    }
}
