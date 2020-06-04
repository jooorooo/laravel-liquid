<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 22.3.2019 г.
 * Time: 12:44 ч.
 */

namespace Liquid\Traits;

use Illuminate\Support\Str;
use Liquid\LiquidCompiler;
use Liquid\TemplateContent;
use Liquid\Tokens\GuessToken;
use Liquid\Tokens\TagToken;
use Liquid\Tokens\TextToken;
use Liquid\Tokens\VariableToken;

trait TokenizeTrait
{

    /**
     * Tokenizes the given source string
     *
     * @param TemplateContent $source
     *
     * @return array|TagToken[]|TextToken[]|VariableToken[]
     */
    public function tokenize(TemplateContent $source)
    {
        $tokens = preg_split('/(' . LiquidCompiler::PARTIAL_TEMPLATE_PARSER . ')/ms', ($content = str_replace(["\r\n", "\r"], "\n", $source->getContent())), null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);

        $tokens = array_map(function($token) {
            $tokens = preg_split('/(' . LiquidCompiler::PARTIAL_TEMPLATE_PARSER . ')/', $token[0], null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
            if(count($tokens) > 1) {
                return array_map(function($t) use($token) {
                    $t[1] = $t[1] + $token[1];
                    return $t;
                }, $tokens);
            }
            return [$token];
        }, $tokens);

        if(empty($tokens)) {
            return [];
        }

        if(count($tokens) == 1) {
            $tokens = array_shift($tokens);
        } else {
            $tokens = call_user_func_array('array_merge', $tokens);
        }

        $tokens = array_map(function($token) use($content) {
            return (new GuessToken($token[1], $token[0]))->parseType($content);
        }, $tokens);

        if(empty($tokens)) {
            return [];
        }

        if(count($tokens) == 1) {
            return array_shift($tokens);
        } else {
            return call_user_func_array('array_merge', $tokens);
        }
    }

}
