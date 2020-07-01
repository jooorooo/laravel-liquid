<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Liquid\Helpers\Color;

class ColorFilters extends AbstractFilters
{
    public function color_to_rgb($input)
    {
        return Color::parse($input)->toCssRgb();
    }

    public function color_to_hsl($input)
    {
        return Color::parse($input)->toCssHsl();
    }

    public function color_to_hex($input)
    {
        return Color::parse($input)->toCssHex();
    }

    public function color_extract($input, $component)
    {
        return Color::parse($input)->getComponent($component);
    }

    public function color_brightness($input)
    {
        return Color::parse($input)->getComponent('brightness')['total'] ?? null;
    }

    public function color_modify($input, $component, $range)
    {
        $instance = Color::parse($input);
        if($component && is_string($component) && method_exists($instance, $method = sprintf('modify%s', ucfirst(strtolower($component))))) {
            $instance = $instance->$method($range);
        }

        return $instance->toCss();
    }

    public function color_lighten($input, $value)
    {
        return Color::parse($input)->lighten($value)->toCss();
    }

    public function color_darken($input, $value)
    {
        return Color::parse($input)->darken($value)->toCss();
    }

    public function color_saturate($input, $value)
    {
        return Color::parse($input)->saturate($value)->toCss();
    }

    public function color_desaturate($input, $value)
    {
        return Color::parse($input)->desaturate($value)->toCss();
    }

    public function color_mix($input, $color, $blend)
    {
        return Color::parse($input)->mix($color, $blend)->toCss();
    }

    public function color_contrast($input, $color)
    {
        return Color::parse($input)->contrast($color);
    }

    public function color_difference($input, $color)
    {
        return Color::parse($input)->difference($color);
    }

    public function brightness_difference($input, $color)
    {
        return Color::parse($input)->brightnessDifference($color);
    }

}
