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

    private $_source;
    private $_code;
    private $_cursor;
    private $_lineno;
    private $_end;
    private $_tokens = [];
    private $_state;
    private $_states = [];
    private $_brackets = [];
    private $_positions = [];
    private $_position = -1;
    private $_currentVarBlockLine;

    /**
     * Tokenizes the given source string
     *
     * @param TemplateContent $source
     *
     * @return array|TagToken[]|TextToken[]|VariableToken[]
     */
    public function tokenize(TemplateContent $source)
    {
        $this->_source = $source;
        $this->_code = str_replace(["\r\n", "\r"], "\n", $source->getContent());
        $this->_cursor = 0;
        $this->_lineno = 1;
        $this->_end = Str::length($this->_code);
        $this->_tokens = [];
        $this->_state = 0;
        $this->_states = [];
        $this->_brackets = [];
        $this->_position = -1;

        preg_match_all('/('.LiquidCompiler::ANY_STARTING_TAG.')(-|~)?/sx', $this->_code, $matches, PREG_OFFSET_CAPTURE);
        $this->_positions = $matches;

        while ($this->_cursor < $this->_end) {
            switch ($this->_state) {
                case 0:
                    $this->lexData();
                break;
                case 2:
                    $this->lexExpression();
                    break;
            }
        }

        dd($matches);
    }

    private function lexExpression()
    {
        // whitespace
        if (preg_match('/\s+/A', $this->_code, $match, null, $this->_cursor)) {
            $this->moveCursor($match[0]);

            if ($this->_cursor >= $this->_end) {
                throw new SyntaxError(sprintf('Unclosed "%s".', 1 === $this->_state ? 'block' : 'variable'), $this->_currentVarBlockLine, $this->source);
            }
        }

        dd($this->_code[$this->_cursor]);

        // operators
        if (preg_match($this->regexes['operator'], $this->_code, $match, null, $this->_cursor)) {
            $this->pushToken(/* Token::OPERATOR_TYPE */ 8, preg_replace('/\s+/', ' ', $match[0]));
            $this->moveCursor($match[0]);
        }
        // names
        elseif (preg_match(self::REGEX_NAME, $this->_code, $match, null, $this->_cursor)) {
            $this->pushToken(/* Token::NAME_TYPE */ 5, $match[0]);
            $this->moveCursor($match[0]);
        }
        // numbers
        elseif (preg_match(self::REGEX_NUMBER, $this->_code, $match, null, $this->_cursor)) {
            $number = (float) $match[0];  // floats
            if (ctype_digit($match[0]) && $number <= PHP_INT_MAX) {
                $number = (int) $match[0]; // integers lower than the maximum
            }
            $this->pushToken(/* Token::NUMBER_TYPE */ 6, $number);
            $this->moveCursor($match[0]);
        }
        // punctuation
        elseif (false !== strpos(self::PUNCTUATION, $this->_code[$this->_cursor])) {
            // opening bracket
            if (false !== strpos('([{', $this->_code[$this->_cursor])) {
                $this->brackets[] = [$this->_code[$this->_cursor], $this->lineno];
            }
            // closing bracket
            elseif (false !== strpos(')]}', $this->_code[$this->_cursor])) {
                if (empty($this->brackets)) {
                    throw new SyntaxError(sprintf('Unexpected "%s".', $this->_code[$this->_cursor]), $this->lineno, $this->source);
                }

                list($expect, $lineno) = array_pop($this->brackets);
                if ($this->_code[$this->_cursor] != strtr($expect, '([{', ')]}')) {
                    throw new SyntaxError(sprintf('Unclosed "%s".', $expect), $lineno, $this->source);
                }
            }

            $this->pushToken(/* Token::PUNCTUATION_TYPE */ 9, $this->_code[$this->_cursor]);
            ++$this->_cursor;
        }
        // strings
        elseif (preg_match(self::REGEX_STRING, $this->_code, $match, null, $this->_cursor)) {
            $this->pushToken(/* Token::STRING_TYPE */ 7, stripcslashes(substr($match[0], 1, -1)));
            $this->moveCursor($match[0]);
        }
        // opening double quoted string
        elseif (preg_match(self::REGEX_DQ_STRING_DELIM, $this->_code, $match, null, $this->_cursor)) {
            $this->brackets[] = ['"', $this->lineno];
            $this->pushState(self::STATE_STRING);
            $this->moveCursor($match[0]);
        }
        // unlexable
        else {
            throw new SyntaxError(sprintf('Unexpected character "%s".', $this->_code[$this->_cursor]), $this->lineno, $this->source);
        }
    }

    private function lexData()
    {
        // if no matches are left we return the rest of the template as simple text token
        if ($this->_position == \count($this->_positions[0]) - 1) {
            $this->pushToken(/* Token::TEXT_TYPE */ 0, Str::substr($this->_code, $this->_cursor));
            $this->_cursor = $this->_end;

            return;
        }

        // Find the first token after the current cursor
        $position = $this->_positions[0][++$this->_position];
        while ($position[1] < $this->_cursor) {
            if ($this->_position == \count($this->_positions[0]) - 1) {
                return;
            }
            $position = $this->_positions[0][++$this->_position];
        }

        // push the template text first
        $text = $textContent = Str::substr($this->_code, $this->_cursor, $position[1] - $this->_cursor);

        // trim?
        if (isset($this->_positions[2][$this->_position][0])) {
            if ('-' === $this->_positions[2][$this->_position][0]) {
                // whitespace_trim detected ({%-, {{- or {#-)
                $text = rtrim($text);
            } else {
                // whitespace_line_trim detected ({%~, {{~ or {#~)
                // don't trim \r and \n
                $text = rtrim($text, " \t\0\x0B");
            }
        }

        $this->pushToken(/* Token::TEXT_TYPE */ 0, $text);
        $this->moveCursor($textContent.$position[0]);

        switch ($this->_positions[1][$this->_position][0]) {
            case LiquidCompiler::OPERATION_TAGS[0]:
                dd($this, $text);
                // raw data?
                if (preg_match($this->regexes['lex_block_raw'], $this->_code, $match, null, $this->_cursor)) {
                    $this->moveCursor($match[0]);
                    $this->lexRawData();
                    // {% line \d+ %}
                } elseif (preg_match($this->regexes['lex_block_line'], $this->_code, $match, null, $this->_cursor)) {
                    $this->moveCursor($match[0]);
                    $this->lineno = (int) $match[1];
                } else {
                    $this->pushToken(/* Token::BLOCK_START_TYPE */ 1);
                    $this->pushState(self::STATE_BLOCK);
                    $this->_currentVarBlockLine = $this->lineno;
                }
                break;

            case LiquidCompiler::VARIABLE_TAG[0]:
                $this->pushToken(/* Token::VAR_START_TYPE */ 2);
                $this->pushState(2);
                $this->_currentVarBlockLine = $this->_lineno;
                break;
        }
    }

    private function pushToken($type, $value = '')
    {
        // do not push empty text tokens
        if (/* Token::TEXT_TYPE */ 0 === $type && '' === $value) {
            return;
        }

        $this->_tokens[] = [$type, $value, $this->_lineno];
        //$this->_tokens[] = new Token($type, $value, $this->_lineno);
    }

    private function moveCursor($text)
    {
        $this->_cursor += Str::length($text);
        $this->_lineno += substr_count($text, "\n");
    }

    private function pushState($state)
    {
        $this->_states[] = $this->_state;
        $this->_state = $state;
    }

}
