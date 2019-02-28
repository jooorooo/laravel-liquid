<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\ViewFinderInterface;
use Liquid\Tag\TagInclude;
use Liquid\Tag\TagExtends;

/**
 * This class represents the entire template document.
 */
class Document extends AbstractBlock
{
    /**
     * Constructor.
     *
     * @param array $tokens
     * @param ViewFinderInterface $viewFinder
     * @param Filesystem|null $files
     * @param null $compiled
     * @throws LiquidException
     */
	public function __construct(array &$tokens, ViewFinderInterface $viewFinder = null, Filesystem $files = null, $compiled = null) {
		$this->viewFinder = $viewFinder;
        $this->files = $files;
        $this->compiled = $compiled;
		$this->parse($tokens);
	}

    /**
     * Check for cached includes
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
	public function checkIncludes() {
		foreach ($this->nodelist as $token) {
			if (is_object($token)) {
				if ($token instanceof TagInclude || $token instanceof TagExtends) {
					/** @var TagInclude|TagExtends $token */
					if ($token->checkIncludes() == true) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * There isn't a real delimiter
	 *
	 * @return string
	 */
	protected function blockDelimiter() {
		return '';
	}

	/**
	 * Document blocks don't need to be terminated since they are not actually opened
	 */
	protected function assertMissingDelimitation() {
	}
}
