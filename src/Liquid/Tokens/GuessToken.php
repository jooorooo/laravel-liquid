<?php

namespace Liquid\Tokens;

use Illuminate\Support\Str;
use Liquid\LiquidCompiler;

class GuessToken
{
    protected $start;
    protected $end;
    protected $line;
    protected $code;
    protected $source;
    protected $name;

    public function __construct($offset, $code, $source = null)
    {
        $this->start = $offset;
        $this->code = $code;
        $this->end = $this->start + strlen($this->code);
        if(!is_null($source)) {
            $this->line = substr_count(substr($source, 0, $this->start + 1), "\n") + 1;
            $this->source = $source;
        }
    }

    public function parseType($source)
    {
        if(preg_match('/^(.*)?(' . LiquidCompiler::VARIABLE_TAG[0] . '(-)?(.+)(-)?' . LiquidCompiler::VARIABLE_TAG[1] . ')(.*)?$/ms', $this->code, $match)) {
            $return = [];
            $start = $this->start;
            if(!empty($match[1])) {
                $return[] = new TextToken($start, $match[1], $source);
                $start += Str::length($match[1]);
            }
            if(!empty($match[2])) {
                $filters = [];
                if($match[3] == '-') {
                    $filters[] = 'lstrip';
                }
                if(substr($match[4], -1) == '-') {
                    $filters[] = 'rstrip';
                    $match[4] = Str::substr($match[4], 0, -1);
                }
                $return[] = new VariableToken($start, $match[2], $source, trim($match[4]), $filters);
                $start += Str::length($match[2]);
            }
            if(!empty($match[6])) {
                $return[] = new TextToken($start, $match[6], $source);
            }
            return $return;
        } elseif(preg_match('/^(.*)?(' . LiquidCompiler::OPERATION_TAGS[0] . '(-)?.*?((\w+)(.*))(-)?' . LiquidCompiler::OPERATION_TAGS[1] . ')(.*)?$/ms', $this->code, $match)) {
            $return = [];
            $start = $this->start;
            if(!empty($match[1])) {
                $return[] = new TextToken($start, $match[1], $source);
                $start += Str::length($match[1]);
            }
            if(!empty($match[2])) {
                $filters = [];
                if($match[3] == '-') {
                    $filters[] = 'lstrip';
                }
                if(substr($match[4], -1) == '-') {
                    $filters[] = 'rstrip';
                    $match[4] = Str::substr($match[4], 0, -1);
                }
                $return[] = new TagToken($start, $match[2], $source, $match[5], $match[4], trim($match[6]), $filters);
                $start += Str::length($match[2]);
            }
            if(!empty($match[8])) {
                $return[] = new TextToken($start, $match[8], $source);
            }
            return $return;
        } else {
            return [
                new TextToken($this->start, $this->code, $source)
            ];
        }
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return str_replace('.', '/', $this->name) . '.liquid';
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source): void
    {
        $this->source = $source;
    }
}
