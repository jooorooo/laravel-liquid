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

use Illuminate\Filesystem\Filesystem;
use Liquid\AbstractTag;
use Liquid\Document;
use Liquid\Context;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;

/**
 * https://github.com/harrydeluxe/php-liquid/wiki/Template-Inheritance
 * Extends a template by another one.
 *
 * Example:
 *
 *     {% layout "base" %}
 */
class TagLayout extends AbstractTag
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
     * @param Filesystem|null $files
     * @param null $compiled
     * @throws LiquidException
     */
    public function __construct($markup, array &$tokens, Filesystem $files = null, $compiled = null)
    {
        $regex = new Regexp('/("[^"]+"|\'[^\']+\')?/');

        if ($regex->match($markup)) {
            $this->templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
        } else {
            throw new LiquidException("Error in tag 'layout' - Valid syntax: layout '[template name]'");
        }

        parent::__construct($markup, $tokens, $files, $compiled);
    }

    /**
     * @param array $tokens
     *
     * @return array
     */
    private function findBlocks(array $tokens)
    {
        $blockstartRegexp = new Regexp('/^' . LiquidCompiler::OPERATION_TAGS[0] . '\s*block (\w+)\s*(.*)?' . LiquidCompiler::OPERATION_TAGS[1] . '$/');
        $blockendRegexp = new Regexp('/^' . LiquidCompiler::OPERATION_TAGS[0] . '\s*endblock\s*?' . LiquidCompiler::OPERATION_TAGS[1] . '$/');

        $b = array();
        $name = null;

        foreach ($tokens as $token) {
            if ($blockstartRegexp->match($token)) {
                $name = $blockstartRegexp->matches[1];
                $b[$name] = array();
            } else if ($blockendRegexp->match($token)) {
                $name = null;
            } else {
                if ($name !== null) {
                    array_push($b[$name], $token);
                }
            }
        }

        return $b;
    }

    /**
     * Parses the tokens
     *
     * @param array $tokens
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function parse(array &$tokens)
    {
        // read the source of the template and create a new sub document
        $source = $this->files->get(app('view.finder')->find($this->templateName));

        // tokens in this new document
        $maintokens = LiquidCompiler::tokenize($source);

        $eRegexp = new Regexp('/^' . LiquidCompiler::OPERATION_TAGS[0] . '\s*layout (.*)?' . LiquidCompiler::OPERATION_TAGS[1] . '$/');
        foreach ($maintokens as $maintoken)
            if ($eRegexp->match($maintoken)) {
                $m = $eRegexp->matches[1];
                break;
            }

        if (isset($m)) {
            $rest = array_merge($maintokens, $tokens);
        } else {
            $childtokens = $this->findBlocks($tokens);

            $blockstartRegexp = new Regexp('/^' . LiquidCompiler::OPERATION_TAGS[0] . '\s*block (\w+)\s*(.*)?' . LiquidCompiler::OPERATION_TAGS[1] . '$/');
            $blockendRegexp = new Regexp('/^' . LiquidCompiler::OPERATION_TAGS[0] . '\s*endblock\s*?' . LiquidCompiler::OPERATION_TAGS[1] . '$/');

            $name = null;

            $rest = array();
            $aufzeichnen = false;

            for ($i = 0; $i < count($maintokens); $i++) {
                if ($blockstartRegexp->match($maintokens[$i])) {
                    $name = $blockstartRegexp->matches[1];

                    if (isset($childtokens[$name])) {
                        $aufzeichnen = true;
                        array_push($rest, $maintokens[$i]);
                        foreach ($childtokens[$name] as $item) {
                            array_push($rest, $item);
                        }
                    }

                }
                if (!$aufzeichnen) {
                    array_push($rest, $maintokens[$i]);
                }

                if ($blockendRegexp->match($maintokens[$i]) && $aufzeichnen === true) {
                    $aufzeichnen = false;
                    array_push($rest, $maintokens[$i]);
                }
            }
        }

        $this->document = new Document(null, $rest, $this->files);
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
        $context->push();
        $result = $this->document->render($context);
        $context->pop();
        return $result;
    }
}
