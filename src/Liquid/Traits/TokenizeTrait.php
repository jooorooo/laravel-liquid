<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 22.3.2019 г.
 * Time: 12:44 ч.
 */

namespace Liquid\Traits;

use Liquid\Lexer;
use Liquid\LiquidCompiler;
use Liquid\Token;

trait TokenizeTrait
{

    /**
     * Tokenizes the given source string
     *
     * @param string $source
     *
     * @return array|Token[]
     */
    public function tokenize($source)
    {
//        $l = new Lexer();
//        dd($l->tokenize($source));

        return empty($source)
            ? array()
            //: preg_split('/(' . LiquidCompiler::OPERATION_TAGS[0] . '.*' . LiquidCompiler::OPERATION_TAGS[1] . '|' . LiquidCompiler::VARIABLE_TAG[0] . '.*' . LiquidCompiler::VARIABLE_TAG[1] . ')/ms', $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            : preg_split('/(' . LiquidCompiler::PARTIAL_TEMPLATE_PARSER . ')/ms', $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

//        $tokens = empty($source)
//            ? array()
//            //: preg_split('/(' . LiquidCompiler::OPERATION_TAGS[0] . '.*' . LiquidCompiler::OPERATION_TAGS[1] . '|' . LiquidCompiler::VARIABLE_TAG[0] . '.*' . LiquidCompiler::VARIABLE_TAG[1] . ')/ms', $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
//            : preg_split('/(' . LiquidCompiler::PARTIAL_TEMPLATE_PARSER . ')/ms', $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
//
//
//        return array_map(function($token) {
//            return new Token($token[0], $token[1]);
//        }, $tokens);
    }

}