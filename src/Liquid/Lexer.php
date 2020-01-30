<?php

namespace Liquid;

use Liquid\Exceptions\SyntaxError;

class Lexer
{

    private $tokens;
    private $code;
    private $cursor;
    private $lineno;
    private $end;
    private $state;
    private $states;
    private $brackets;
    private $env;
    private $source;
    private $options;
    private $regexes;
    private $position;
    private $positions;
    private $currentVarBlockLine;

    const STATE_DATA = 0;
    const STATE_BLOCK = 1;
    const STATE_VAR = 2;

    public function tokenize($source)
    {
        $this->source = $source;
        $this->code = $source;//str_replace(["\r\n", "\r"], "\n", $source);
        $this->cursor = 0;
        $this->lineno = 1;
        $this->end = \strlen($this->code);
        $this->tokens = [];
        $this->state = 0;
        $this->states = [];
        $this->brackets = [];
        $this->position = -1;

        preg_match_all('/(' . LiquidCompiler::ANY_STARTING_TAG . ')/', $this->code, $matches, PREG_OFFSET_CAPTURE);
        $this->positions = $matches;

        $help = 0;
        while ($this->cursor < $this->end) {
            switch ($this->state)
            {
                case 0: //data
                    $this->lexData();
                    break;
                case 1: //block
                    $this->lexBlock();
                    break;
                case 2: //var
                    $this->lexVar();
                    break;
            }
        }

        $this->pushToken(/* Token::EOF_TYPE */ -1);

        if (!empty($this->brackets)) {
            list($expect, $lineno) = array_pop($this->brackets);
            throw new SyntaxError(sprintf('Unclosed "%s".', $expect), $lineno, $this->source);
        }

        return $this->tokens;
    }

    private function lexVar()
    {
        if (empty($this->brackets) && preg_match('/' . LiquidCompiler::VARIABLE_TAG[0] . '(.*)' . LiquidCompiler::VARIABLE_TAG[1] . '/isU', $this->code, $match, null, $this->cursor)) {
            $this->pushToken(/* Token::VAR_END_TYPE */ 2, $match[0]);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    private function lexBlock()
    {
        if (empty($this->brackets) && preg_match('/' . LiquidCompiler::OPERATION_TAGS[0] . '\s*(\w+)\s*(.*)?' . LiquidCompiler::OPERATION_TAGS[1] . '/isU', $this->code, $match, null, $this->cursor)) {
            $this->pushToken(/* Token::BLOCK_END_TYPE */ 1, $match[0]);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    private function lexData()
    {
        // if no matches are left we return the rest of the template as simple text token
        if ($this->position == \count($this->positions[0]) - 1) {
            $this->pushToken(/* Token::TEXT_TYPE */ 0, mb_substr($this->code, $this->cursor, null, 'utf-8'));
            $this->cursor = $this->end;

            return;
        }

        // Find the first token after the current cursor
        $position = $this->positions[0][++$this->position];
        while ($position[1] < $this->cursor) {
            if ($this->position == \count($this->positions[0]) - 1) {
                return;
            }
            $position = $this->positions[0][++$this->position];
        }

        // push the template text first
        $text = $textContent = mb_substr($this->code, $this->cursor, $position[1] - $this->cursor, 'utf-8');

//        // trim?
//        if (isset($this->positions[2][$this->position][0])) {
//            // whitespace_line_trim detected ({%~, {{~ or {#~)
//            // don't trim \r and \n
//            $text = rtrim($text, " \t\0\x0B");
//        }
        $this->pushToken(/* Token::TEXT_TYPE */ 0, $text);
        $this->moveCursor($textContent/*.$position[0]*/);

        switch ($this->positions[1][$this->position][0]) {
            case LiquidCompiler::OPERATION_TAGS[0]:
                //$this->pushToken(/* Token::BLOCK_START_TYPE */ 1);
                $this->pushState(1);
                $this->currentVarBlockLine = $this->lineno;
                break;
            case LiquidCompiler::VARIABLE_TAG[0]:
                //$this->pushToken(/* Token::VAR_START_TYPE */ 2);
                $this->pushState(2);
                $this->currentVarBlockLine = $this->lineno;
                break;
        }
    }

    private function lexExpression()
    {
        // whitespace
        if (preg_match('/\s+/A', $this->code, $match, null, $this->cursor)) {
            $this->moveCursor($match[0]);

            if ($this->cursor >= $this->end) {
                throw new SyntaxError(sprintf('Unclosed "%s".', self::STATE_BLOCK === $this->state ? 'block' : 'variable'), $this->currentVarBlockLine, $this->source);
            }
        }

//        // operators
//        if (preg_match($this->regexes['operator'], $this->code, $match, null, $this->cursor)) {
//            $this->pushToken(/* Token::OPERATOR_TYPE */ 8, preg_replace('/\s+/', ' ', $match[0]));
//            $this->moveCursor($match[0]);
//        }
//        // names
//        elseif (preg_match(self::REGEX_NAME, $this->code, $match, null, $this->cursor)) {
//            $this->pushToken(/* Token::NAME_TYPE */ 5, $match[0]);
//            $this->moveCursor($match[0]);
//        }
//        // numbers
//        elseif (preg_match(self::REGEX_NUMBER, $this->code, $match, null, $this->cursor)) {
//            $number = (float) $match[0];  // floats
//            if (ctype_digit($match[0]) && $number <= PHP_INT_MAX) {
//                $number = (int) $match[0]; // integers lower than the maximum
//            }
//            $this->pushToken(/* Token::NUMBER_TYPE */ 6, $number);
//            $this->moveCursor($match[0]);
//        }
//        // punctuation
//        elseif (false !== strpos(self::PUNCTUATION, $this->code[$this->cursor])) {
//            // opening bracket
//            if (false !== strpos('([{', $this->code[$this->cursor])) {
//                $this->brackets[] = [$this->code[$this->cursor], $this->lineno];
//            }
//            // closing bracket
//            elseif (false !== strpos(')]}', $this->code[$this->cursor])) {
//                if (empty($this->brackets)) {
//                    throw new SyntaxError(sprintf('Unexpected "%s".', $this->code[$this->cursor]), $this->lineno, $this->source);
//                }
//
//                list($expect, $lineno) = array_pop($this->brackets);
//                if ($this->code[$this->cursor] != strtr($expect, '([{', ')]}')) {
//                    throw new SyntaxError(sprintf('Unclosed "%s".', $expect), $lineno, $this->source);
//                }
//            }
//
//            $this->pushToken(/* Token::PUNCTUATION_TYPE */ 9, $this->code[$this->cursor]);
//            ++$this->cursor;
//        }
//        // strings
//        elseif (preg_match(self::REGEX_STRING, $this->code, $match, null, $this->cursor)) {
//            $this->pushToken(/* Token::STRING_TYPE */ 7, stripcslashes(substr($match[0], 1, -1)));
//            $this->moveCursor($match[0]);
//        }
//        // opening double quoted string
//        elseif (preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, null, $this->cursor)) {
//            $this->brackets[] = ['"', $this->lineno];
//            $this->pushState(self::STATE_STRING);
//            $this->moveCursor($match[0]);
//        }
//        // unlexable
//        else {
//            throw new SyntaxError(sprintf('Unexpected character "%s".', $this->code[$this->cursor]), $this->lineno, $this->source);
//        }
    }

    private function pushToken($type, $value = '')
    {
        // do not push empty text tokens
        if (/* Token::TEXT_TYPE */ 0 === $type && '' === $value) {
            return;
        }

        $this->tokens[] = new Token($type, $value, $this->lineno);
    }

    private function moveCursor($text)
    {
        $this->cursor += \mb_strlen($text, 'utf-8');
        $this->lineno += substr_count($text, "\n");
    }

    private function pushState($state)
    {
        $this->states[] = $this->state;
        $this->state = $state;
    }

    private function popState()
    {
        if (0 === \count($this->states)) {
            throw new \LogicException('Cannot pop state without a previous state.');
        }

        $this->state = array_pop($this->states);
    }

}