<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 17:35 ч.
 */

namespace Liquid\Filters;

use Liquid\Exceptions\BaseFilterError;
use Liquid\Exceptions\FilterError;
use Liquid\Helpers\Color;

class ColorFilters extends AbstractFilters
{
    public function color_to_rgb($input)
    {
        try {
            return Color::parse($input)->toCssRgb();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_to_hsl($input)
    {
        try {
            return Color::parse($input)->toCssHsl();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_to_hex($input)
    {
        try {
            return Color::parse($input)->toCssHex();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_extract(...$input)
    {
        try {
            $this->__validate($input, 2, [
                1 => 'scalar',
            ]);

            return Color::parse($input[0])->getComponent($input[1]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_brightness($input)
    {
        try {
            return Color::parse($input)->getComponent('brightness')['total'] ?? null;
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_modify(...$input)
    {
        try {
            $this->__validate($input, 3, [
                1 => 'scalar',
                2 => 'numeric'
            ]);

            $instance = Color::parse($input[0]);
            if($input[1] && is_string($input[1]) && method_exists($instance, $method = sprintf('modify%s', ucfirst(strtolower($input[1]))))) {
                $instance = $instance->$method($input[2]);
            }

            return $instance->toCss();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_lighten(...$input)
    {
        try {
            $this->__validate($input, 2);

            return Color::parse($input[0])->lighten($input[1])->toCss();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_darken(...$input)
    {
        try {
            $this->__validate($input, 2);

            return Color::parse($input[0])->darken($input[1])->toCss();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_saturate(...$input)
    {
        try {
            $this->__validate($input, 2);

            return Color::parse($input[0])->saturate($input[1])->toCss();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_desaturate(...$input)
    {
        try {
            $this->__validate($input, 2);

            return Color::parse($input[0])->desaturate($input[1])->toCss();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_mix(...$input)
    {
        try {
            $this->__validate($input, 3);

            return Color::parse($input[0])->mix($input[1], $input[2])->toCss();
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_contrast(...$input)
    {
        try {
            $this->__validate($input, 2);

            return Color::parse($input[0])->contrast($input[1]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function color_difference(...$input)
    {
        try {
            $this->__validate($input, 2);

            return Color::parse($input[0])->difference($input[1]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

    public function brightness_difference(...$input)
    {
        try {
            $this->__validate($input, 2);

            return Color::parse($input[0])->brightnessDifference($input[1]);
        } catch (BaseFilterError $e) {
            throw new FilterError(sprintf(
                'Liquid error: "%s" %s',
                __FUNCTION__,
                $e->getMessage()
            ), $this->context->getToken());
        }
    }

}
