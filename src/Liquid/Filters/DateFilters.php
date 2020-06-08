<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Carbon\Carbon;

class DateFilters extends AbstractFilters
{

    /**
     * Formats a date using strftime
     *
     * @param mixed $input
     * @param string $format
     *
     * @return string
     */
    public function date($input, $format)
    {
        if($input instanceof Carbon) {
            $input = $input->timestamp;
        } elseif (!is_numeric($input)) {
            $input = strtotime($input);
        }

        if ($format == 'r') {
            return date($format, $input);
        }

        return strftime($format, $input);
    }

}
