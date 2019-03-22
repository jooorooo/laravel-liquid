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
