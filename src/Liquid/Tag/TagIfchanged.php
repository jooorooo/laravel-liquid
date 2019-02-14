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

use Illuminate\View\ViewFinderInterface;
use Liquid\AbstractBlock;
use Liquid\Context;

/**
 * Quickly create a table from a collection
 */
class TagIfchanged extends AbstractBlock
{
	/**
	 * The last value
	 *
	 * @var string
	 */
	private $lastValue = '';

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param ViewFinderInterface $viewFinder
	 *
	 * @throws \Liquid\LiquidException
	 */
	public function __construct($markup, array &$tokens, ViewFinderInterface $viewFinder = null) {
		parent::__construct($markup, $tokens, $viewFinder);
	}

	/**
	 * Renders the block
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context) {
		$output = parent::render($context);

		if ($this->lastValue == $output) {
			return '';
		} else {
			$this->lastValue = $output;
			return $this->lastValue;		
		}
		
	}
}
