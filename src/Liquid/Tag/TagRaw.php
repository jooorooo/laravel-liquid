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
use Liquid\LiquidCompiler;
use Liquid\Regexp;
use Liquid\Tokens\TagToken;
use Liquid\Tokens\TextToken;
use Liquid\Tokens\VariableToken;
use ReflectionException;

/**
 * Allows output of Liquid code on a page without being parsed.
 *
 * Example:
 *
 *     {% raw %}{{ 5 | plus: 6 }}{% endraw %} is equal to 11.
 *
 *     will return:
 *     {{ 5 | plus: 6 }} is equal to 11.
 */
class TagRaw extends AbstractBlock
{
    /**
     * @param array $tokens
     */
    public function parse(array &$tokens)
    {
        $this->nodelist = array();

        if(is_array($tokens)) {
            while (count($tokens)) {
                /** @var TagToken|TextToken|VariableToken */
                $token = array_shift($tokens);

                if ($token instanceof TagToken && $token->getTag() === $this->blockDelimiter()) {
                    return;
                }

                $this->nodelist[] = $token;
            }
        }
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
        /** @var TagToken|TextToken|VariableToken */
        foreach ($list as $token) {
            $result .= $token->getCode();
        }

        return $result;
    }
}
