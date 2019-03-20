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
use Liquid\AbstractBlock;
use Liquid\LiquidException;
use Liquid\Regexp;

/**
 * Marks a section of a template as being reusable.
 *
 * Example:
 *
 *     {% block foo %} bar {% endblock %}
 */
class TagBlock extends AbstractBlock
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private $block;

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
    public function __construct($markup, array &$tokens, ViewFinderInterface $viewFinder = null, Filesystem $files = null, $compiled = null)
    {
        $syntaxRegexp = new Regexp('/(\w+)/');

        if ($syntaxRegexp->match($markup)) {
            $this->block = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens, $viewFinder, $files, $compiled);
        } else {
            throw new LiquidException("Syntax Error in 'block' - Valid syntax: block [name]");
        }
    }
}
