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

/**
 * This class represents the entire template document.
 */
class Document extends AbstractBlock
{
    /**
     * Constructor.
     *
     * @param array $tokens
     * @param Filesystem|null $files
     * @param null $compiled
     * @throws LiquidException
     * @throws \ReflectionException
     */
    public function __construct(array &$tokens, Filesystem $files = null, $compiled = null)
    {
        $this->files = $files;
        $this->compiled = $compiled;
        $this->parse($tokens);
    }

    /**
     * There isn't a real delimiter
     *
     * @return string
     */
    protected function blockDelimiter()
    {
        return '';
    }

    /**
     * Document blocks don't need to be terminated since they are not actually opened
     */
    protected function assertMissingDelimitation()
    {
    }
}
