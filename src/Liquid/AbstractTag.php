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

/**
 * Base class for tags.
 */
abstract class AbstractTag
{
    /**
     * The markup for the tag
     *
     * @var string
     */
    protected $markup;

    /**
     * Filesystem object is used to load included template files
     *
     * @var ViewFinderInterface
     */
    protected $viewFinder;

    /**
     * Filesystem object is used to store and load compiled files
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * compiled dir
     *
     * @var string
     */
    protected $compiled;

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Constructor.
     *
     * @param string $markup
     * @param array $tokens
     * @param ViewFinderInterface $viewFinder
     * @param Filesystem|null $files
     * @param null $compiled
     */
    public function __construct($markup, array &$tokens, ViewFinderInterface $viewFinder = null, Filesystem $files = null, $compiled = null)
    {
        $this->markup = $markup;
        $this->viewFinder = $viewFinder;
        $this->files = $files;
        $this->compiled = $compiled;
        $this->parse($tokens);
    }

    /**
     * Parse the given tokens.
     *
     * @param array $tokens
     */
    public function parse(array &$tokens)
    {
        // Do nothing by default
    }

    /**
     * Render the tag with the given context.
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        return '';
    }

    /**
     * Extracts tag attributes from a markup string.
     *
     * @param string $markup
     */
    protected function extractAttributes($markup)
    {
        $this->attributes = array();

        $attributeRegexp = new Regexp(Liquid::get('TAG_ATTRIBUTES'));

        $matches = $attributeRegexp->scan($markup);

        foreach ($matches as $match) {
            $this->attributes[$match[0]] = $match[1];
        }
    }

    /**
     * Returns the name of the tag.
     *
     * @return string
     */
    protected function name()
    {
        return strtolower(get_class($this));
    }
}
