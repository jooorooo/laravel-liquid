<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 22.3.2019 г.
 * Time: 12:44 ч.
 */

namespace Liquid\Traits;

use Liquid\LiquidCompiler;

trait TokenizeTrait
{

    /**
     * Tokenizes the given source string
     *
     * @param string $source
     *
     * @return array
     */
    public function tokenize($source)
    {

        return empty($source)
            ? array()
            : preg_split('/(' . LiquidCompiler::PARTIAL_TEMPLATE_PARSER . ')/ms', $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

}
