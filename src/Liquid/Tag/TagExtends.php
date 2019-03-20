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
use Illuminate\View\ViewFinderInterface;
use Liquid\AbstractTag;
use Liquid\Document;
use Liquid\Context;
use Liquid\LiquidEngine;
use Liquid\LiquidException;
use Liquid\Regexp;

/**
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
	 * @var string The Source Hash
	 */
	protected $hash;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param ViewFinderInterface $viewFinder
     *
     * @param Filesystem|null $files
     * @param null $compiled
     * @throws LiquidException
     */
	public function __construct($markup, array &$tokens, ViewFinderInterface $viewFinder = null, Filesystem $files = null, $compiled = null) {
		$regex = new Regexp('/("[^"]+"|\'[^\']+\')?/');

		if ($regex->match($markup)) {
			$this->templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
		} else {
			throw new LiquidException("Error in tag 'extends' - Valid syntax: extends '[template name]'");
		}

		parent::__construct($markup, $tokens, $viewFinder, $files, $compiled);
	}

	/**
	 * @param array $tokens
	 *
	 * @return array
	 */
	private function findBlocks(array $tokens) {
		$blockstartRegexp = new Regexp('/^' . LiquidEngine::TAG_START . '\s*block (\w+)\s*(.*)?' . LiquidEngine::TAG_END . '$/');
		$blockendRegexp = new Regexp('/^' . LiquidEngine::TAG_START . '\s*endblock\s*?' . LiquidEngine::TAG_END . '$/');

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
	 *
	 * @throws \Liquid\LiquidException
	 */
	public function parse(array &$tokens) {
		if ($this->viewFinder === null) {
			throw new LiquidException("No file system");
		}

		// read the source of the template and create a new sub document
		$source = file_get_contents($this->viewFinder->find($this->templateName));

		// tokens in this new document
		$maintokens = LiquidEngine::tokenize($source);

		$eRegexp = new Regexp('/^' . LiquidEngine::TAG_START . '\s*extends (.*)?' . LiquidEngine::TAG_END . '$/');
		foreach ($maintokens as $maintoken)
			if ($eRegexp->match($maintoken)) {
				$m = $eRegexp->matches[1];
				break;
			}

		if (isset($m)) {
			$rest = array_merge($maintokens, $tokens);
		} else {
			$childtokens = $this->findBlocks($tokens);

			$blockstartRegexp = new Regexp('/^' . LiquidEngine::TAG_START . '\s*block (\w+)\s*(.*)?' . LiquidEngine::TAG_END . '$/');
			$blockendRegexp = new Regexp('/^' . LiquidEngine::TAG_START . '\s*endblock\s*?' . LiquidEngine::TAG_END . '$/');

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

		$this->hash = md5($source);

        $file = $this->hash . '.liquid';
        $path = $this->compiled . '/' . $file;
        if(!$this->files->exists($path) || !($this->document = @unserialize($this->files->get($path))) || !($this->document->checkIncludes() != true)) {
            $templateTokens = LiquidEngine::tokenize($source);
            $this->document = new Document($templateTokens, $this->viewFinder, $this->files, $this->compiled);
            $this->files->put($path, serialize($this->document));
        }

	}

    /**
     * check for cached includes
     *
     * @return boolean
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function checkIncludes() {
        if ($this->document->checkIncludes() == true) {
            return true;
        }

        $source = $this->files->get($this->viewFinder->find($this->templateName));

        $file = md5($source) . '.liquid';
        $path = $this->compiled . '/' . $file;

        if ($this->files->exists($path) && $this->hash == md5($source)) {
            return false;
        }

        return true;
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     * @throws LiquidException
     */
	public function render(Context $context) {
		$context->push();
		$result = $this->document->render($context);
		$context->pop();
		return $result;
	}
}
