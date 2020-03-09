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
use Liquid\LiquidCompiler;
use Liquid\Context;

/**
 * Performs an assignment of one variable to another
 *
 * Example:
 *
 *     {% liquid
 *          case 5
 *          when 1
 *          assign column_size = ''
 *          when 2
 *          assign column_size = 'one-half'
 *          when 3
 *          assign column_size = 'one-third'
 *          else
 *          assign column_size = 'one-quarter'
 *     endcase %}
 */
class TagLiquid extends AbstractTag
{

    /**
     * @var Document The Document that represents the included template
     */
    private $document;

    /**
     * Parses the tokens
     *
     * @param array $tokens
     *
     */
    public function parse(array &$tokens)
    {
        $markup = str_replace(["\r\n", "\r"], "\n", $this->markup);
        $templateTokens = array_map(function($line) {
            return sprintf('%s %s %s', LiquidCompiler::OPERATION_TAGS[0], trim($line), LiquidCompiler::OPERATION_TAGS[1]);
        }, array_filter(explode("\n", $markup)));

        $this->document = new Document(null, $templateTokens, $this->compiler);
    }

    /**
     * Renders the node
     *
     * @param Context $context
     * @return mixed|string
     */
    public function render(Context $context)
    {
        return $this->document->render($context);
    }
}