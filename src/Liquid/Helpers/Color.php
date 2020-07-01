<?php

namespace Liquid\Helpers;

class Color
{
    /**
     * @var int $red
     */
    protected $red;
    /**
     * @var int $green
     */
    protected $green;
    /**
     * @var int $blue
     */
    protected $blue;
    /**
     * @var null|float $alpha
     */
    protected $alpha;
    /**
     * @var float $hue
     */
    protected $hue;
    /**
     * @var float $saturation
     */
    protected $saturation;
    /**
     * @var float $lightness
     */
    protected $lightness;
    /**
     * @var float $lightness
     */
    protected $luminance;
    /**
     * @var array $lightness
     */
    protected $brightness;
    /**
     * @var string $type
     */
    protected $type;

    const TYPE_HEX = 'hex';
    const TYPE_RGB = 'rgb';
    const TYPE_HSL = 'hsl';
    const TYPE_WORD = 'word';

    /**
     * Color construct.
     *
     * @param array $data
     * @access protected
     */
    protected function __construct(array $data = [])
    {
        foreach($data as $k=>$v) {
            $this->{$k} = $v;
        }
    }

    /**
     * @param array $rgb
     * @return Color
     */
    public static function fromRgb(array $rgb)
    {
        return (new static(static::formatArrayToRgb($rgb)))
            ->setBrightness()
            ->setHsl()
            ->setLuminance()
            ->setType(static::TYPE_RGB);
    }

    /**
     * @param string $hex
     * @return Color
     */
    public static function fromHex(string $hex)
    {
        if(!in_array($length = strlen($hex = ltrim($hex, '#')), [3,4,6,8])) {
            throw new \Exception('Error hex');
        }

        $map = array_map(function($c) {
            return hexdec(str_pad($c, 2, $c));
        }, str_split($hex, $length > 4 ? 2 : 1));

        if(isset($map[3])) {
            $map[3] = round(((100 / 255) * $map[3]) / 100, 5);
        }

        return static::fromRgb($map)
            ->setType(static::TYPE_HEX);
    }

    /**
     * @param string $color
     * @return null|Color
     */
    public static function fromWord(string $color)
    {
        if(array_key_exists($color = strtolower($color), $colors = static::getWordColors())) {
            return static::fromHex($colors[$color])
                ->setType(static::TYPE_WORD);
        }

        return null;
    }

    /**
     * @param array $hsl
     * @return Color
     */
    public static function fromHsl(array $hsl)
    {
        return (new static(static::formatArrayToHsl($hsl)))
            ->setFromHslArray()
            ->setType(static::TYPE_HSL);
    }

    /**
     * @param $color
     * @return Color
     */
    public static function parse($color)
    {
        $finders_keepers = array(
            '#'    => 'hex',
            'rgba' => 'rgba',
            'rgb'  => 'rgb',
            'hsla' => 'hsla',
            'hsl'  => 'hsl',
        );

        $instance = new static();
        foreach ( $finders_keepers as $finder => $keeper ) {
            if ( false !== strrpos( $color = strtolower($color), $finder ) ) {
                if(method_exists($instance, $method = '_parseFrom' . ucfirst($keeper))) {
                    return $instance->$method($color);
                }
            }
        }

        if($fromWord = static::fromWord($color)) {
            return $fromWord;
        }

        throw new \Exception('Error');
    }

    public function toCssRgb()
    {
        if(!is_null($this->alpha) && $this->alpha > -1 && $this->alpha < 1) {
            return sprintf('rgba(%d, %d, %d, %s)', $this->red, $this->green, $this->blue, $this->alpha);
        }

        return sprintf('rgb(%d, %d, %d)', $this->red, $this->green, $this->blue);
    }

    public function toCssHsl()
    {
        if(!is_null($this->alpha) && $this->alpha > -1 && $this->alpha < 1) {
            return sprintf('hsla(%d, %d%%, %d%%, %s)', $this->hue, $this->saturation, $this->lightness, $this->alpha);
        }

        return sprintf('hsl(%d, %d%%, %d%%)', $this->hue, $this->saturation, $this->lightness);
    }

    public function toCssHex()
    {
        if(!is_null($this->alpha) && $this->alpha > -1 && $this->alpha < 1) {
            return sprintf('#%s%s%s%s', $this->dexhexDoubleDigit($this->red), $this->dexhexDoubleDigit($this->green), $this->dexhexDoubleDigit($this->blue), $this->dexhexDoubleDigit(255*$this->alpha));
        }

        return sprintf('#%s%s%s', $this->dexhexDoubleDigit($this->red), $this->dexhexDoubleDigit($this->green), $this->dexhexDoubleDigit($this->blue));
    }

    public function toCss()
    {
        if($this->type === static::TYPE_HEX) {
            return $this->toCssHex();
        } elseif($this->type === static::TYPE_HSL) {
            return $this->toCssHsl();
        } elseif($this->type === static::TYPE_RGB) {
            return $this->toCssRgb();
        } elseif($this->type === static::TYPE_WORD) {
            return $this->toCssHex();
        }
    }

    //modify
    public function modifyRed($color)
    {
        if(preg_match('/^\d+$/', $color) && $color >= 0 && $color <= 255) {
            $this->red = $color;
        }

        return $this->setBrightness()
            ->setHsl()
            ->setLuminance();
    }

    public function modifyGreen($color)
    {
        if(preg_match('/^\d+$/', $color) && $color >= 0 && $color <= 255) {
            $this->green = $color;
        }

        return $this->setBrightness()
            ->setHsl()
            ->setLuminance();
    }

    public function modifyBlue($color)
    {
        if(preg_match('/^\d+$/', $color) && $color >= 0 && $color <= 255) {
            $this->blue = $color;
        }

        return $this->setBrightness()
            ->setHsl()
            ->setLuminance();
    }

    public function modifyAlpha($alpha)
    {
        if(is_numeric($alpha) && $alpha >= 0 && $alpha <= 1) {
            $this->alpha = $alpha;
        }

        return $this;
    }

    public function modifyHue($hue)
    {
        if(preg_match('/^\d+$/', $hue) && $hue >= 0 && $hue <= 360) {
            $this->hue = $hue === 360 ? 0 : $hue;
        }

        return $this->setFromHslArray();
    }

    public function modifyLightness($lightness)
    {
        if(preg_match('/^\d+$/', $lightness) && $lightness >= 0 && $lightness <= 100) {
            $this->lightness = $lightness;
        }

        return $this->setFromHslArray();
    }

    public function modifySaturation($saturation)
    {
        if(preg_match('/^\d+$/', $saturation) && $saturation >= 0 && $saturation <= 100) {
            $this->saturation = $saturation;
        }

        return $this->setFromHslArray();
    }

    public function lighten($lighten)
    {
        return $this->modifyLightness(min($this->lightness + (int)$lighten, 100));
    }

    public function darken($darken)
    {
        return $this->modifyLightness(max(0, $this->lightness - (int)$darken));
    }

    public function saturate($saturation)
    {
        return $this->modifySaturation(min(100, $this->saturation + $saturation));
    }

    public function desaturate($saturation)
    {
        return $this->modifySaturation(max(0, $this->saturation - $saturation));
    }

    public function mix($color, $blend)
    {
        if(preg_match('/^\d+$/', $blend) && $blend >= 0 && $blend <= 100 && ($color = static::parse($color))) {
            $weight = $blend / 100;
            $f = function ($x) use ($weight) {
                return $weight * $x;
            };

            $g = function ($x) use ($weight) {
                return (1 - $weight) * $x;
            };

            $h = function ($x, $y) {
                return round($x + $y);
            };

            $map = array_map($h, array_map($f, [$this->red, $this->green, $this->blue]), array_map($g, [$color->red, $color->green, $color->blue]));
            return static::fromRgb($map)
                ->setType(static::TYPE_HEX);
        }

        return $this;
    }

    public function contrast($color)
    {
        if($color = static::parse($color)) {
            $levelOne = $this->calculateLuminosity($this);
            $levelTwo = $this->calculateLuminosity($color);
            if($levelOne > $levelTwo){
                return round(($levelOne+0.05) / ($levelTwo+0.05), 2);
            } else {
                return round(($levelTwo+0.05) / ($levelOne+0.05), 2);
            }
        }

        return 0;
    }

    public function difference($color)
    {
        if($color = static::parse($color)) {
            return abs($this->red - $color->red) + abs($this->green - $color->green) + abs($this->blue - $color->blue);
        }

        return 0;
    }

    public function brightnessDifference($color)
    {
        if($color = static::parse($color)) {
            if($this->brightness['total'] > $color->brightness['total']) {
                return $this->brightness['total'] - ($color->brightness['total'] * 100) / $this->brightness['total'];
            } else {
                return $color->brightness['total'] - ($this->brightness['total'] * 100) / $color->brightness['total'];
            }
        }

        return 0;
    }

    public function getComponent($component)
    {
        if(!empty($component)) {
            return $this->{$component} ?? null;
        }

        return null;
    }

    protected function calculateLuminosity(Color $color)
    {
        $r = $color->red / 255; // red value
        $g = $color->green / 255; // green value
        $b = $color->blue / 255; // blue value
        if ($r <= 0.03928) {
            $r = $r / 12.92;
        } else {
            $r = pow((($r + 0.055) / 1.055), 2.4);
        }

        if ($g <= 0.03928) {
            $g = $g / 12.92;
        } else {
            $g = pow((($g + 0.055) / 1.055), 2.4);
        }

        if ($b <= 0.03928) {
            $b = $b / 12.92;
        } else {
            $b = pow((($b + 0.055) / 1.055), 2.4);
        }

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Convert a decimal value to hex and make sure it's 2 characters.
     *
     * @access protected
     *
     * @param int|string $value The value to convert.
     * @return string
     */
    protected function dexhexDoubleDigit( $value ) {
        $value = dechex( $value );
        if ( 1 === strlen( $value ) ) {
            $value = '0' . $value;
        }
        return $value;
    }

    /**
     * Sets the brightness of a color based on the values of red, green, blue.
     *
     * @return Color
     * @access protected
     */
    protected function setBrightness()
    {
        $this->brightness = array(
            'red' => round($this->red * .299),
            'green' => round($this->green * .587),
            'blue' => round($this->blue * .114),
            'total' => round(($this->red * .299) + ($this->green * .587) + ($this->blue * .114), 2),
        );

        return $this;
    }

    /**
     * Sets the luminance of a color (range:0-255) based on the values of red, green, blue.
     *
     * @return Color
     * @access protected
     */
    protected function setLuminance()
    {
        $lum = ( 0.2126 * $this->red ) + ( 0.7152 * $this->green ) + ( 0.0722 * $this->blue );
        $this->luminance = round( $lum );

        return $this;
    }

    /**
     * Set color type
     *
     * @param string $type
     * @return Color
     * @access protected
     */
    protected function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Sets the HSL values of a color based on the values of red, green, blue.
     *
     * @return Color
     * @access protected
     */
    protected function setHsl()
    {
        $red   = $this->red / 255;
        $green = $this->green / 255;
        $blue  = $this->blue / 255;

        $max = max( $red, $green, $blue );
        $min = min( $red, $green, $blue );

        $lightness  = ( $max + $min ) / 2;
        $difference = $max - $min;

        if ( ! $difference ) {
            $hue = $saturation = 0; // Achromatic.
        } else {
            $saturation = $difference / ( 1 - abs( 2 * $lightness - 1 ) );
            switch ( $max ) {
                case $red:
                    $hue = 60 * fmod( ( ( $green - $blue ) / $difference ), 6 );
                    if ( $blue > $green ) {
                        $hue += 360;
                    }
                    break;
                case $green:
                    $hue = 60 * ( ( $blue - $red ) / $difference + 2 );
                    break;
                case $blue:
                    $hue = 60 * ( ( $red - $green ) / $difference + 4 );
                    break;
            }
        }

        $this->hue        = round( $hue );
        $this->saturation = round( $saturation * 100 );
        $this->lightness  = round( $lightness * 100 );

        return $this;
    }

    /**
     * Calculates the red, green, blue values of an HSL color.
     *
     * @return Color
     * @access protected
     *
     * @see https://gist.github.com/brandonheyer/5254516
     */
    protected function setFromHslArray()
    {
        $h = $this->hue / 360;
        $s = $this->saturation / 100;
        $l = $this->lightness / 100;

        $r = $l;
        $g = $l;
        $b = $l;
        $v = ( $l <= 0.5 ) ? ( $l * ( 1.0 + $s ) ) : ( $l + $s - $l * $s );
        if ( $v > 0 ) {
            $m = $l + $l - $v;
            $sv = ( $v - $m ) / $v;
            $h *= 6.0;
            $sextant = floor( $h );
            $fract = $h - $sextant;
            $vsf = $v * $sv * $fract;
            $mid1 = $m + $vsf;
            $mid2 = $v - $vsf;
            switch ( $sextant ) {
                case 0:
                    $r = $v;
                    $g = $mid1;
                    $b = $m;
                    break;
                case 1:
                    $r = $mid2;
                    $g = $v;
                    $b = $m;
                    break;
                case 2:
                    $r = $m;
                    $g = $v;
                    $b = $mid1;
                    break;
                case 3:
                    $r = $m;
                    $g = $mid2;
                    $b = $v;
                    break;
                case 4:
                    $r = $mid1;
                    $g = $m;
                    $b = $v;
                    break;
                case 5:
                    $r = $v;
                    $g = $m;
                    $b = $mid2;
                    break;
            }
        }
        $this->red   = round( $r * 255, 0 );
        $this->green = round( $g * 255, 0 );
        $this->blue  = round( $b * 255, 0 );

        return $this->setLuminance();
    }

    protected function regenerate()
    {

    }

    protected static function formatArrayToRgb(array $map)
    {
        if(count($map) < 3 || count($map) > 4) {
            throw new \Exception('Error array to rgb');
        }

        $result = [];
        foreach(['red', 'green', 'blue', 'alpha'] as $key => $name) {
            if($name === 'alpha' && isset($map[$key])) {
                $result[$name] = $map[$key];
            } elseif($name !== 'alpha') {
                $result[$name] = $map[$key];
            }
        }

        return $result;
    }

    protected static function formatArrayToHsl(array $map)
    {
        if(count($map) < 3 || count($map) > 4) {
            throw new \Exception('Error array to hsl');
        }

        $result = [];
        foreach(['hue', 'saturation', 'lightness', 'alpha'] as $key => $name) {
            if($name === 'hue' && $map[$key] > 359) {
                $map[$key] = 0;
            }

            if($name === 'alpha' && isset($map[$key])) {
                $result[$name] = $map[$key];
            } elseif($name !== 'alpha') {
                $result[$name] = $map[$key];
            }
        }

        return $result;
    }

    /**
     * Gets an array of all the wordcolors.
     *
     * @access protected
     *
     * @return array
     */
    protected static function getWordColors() {
        return array(
            'aliceblue'            => 'F0F8FF',
            'antiquewhite'         => 'FAEBD7',
            'aqua'                 => '00FFFF',
            'aquamarine'           => '7FFFD4',
            'azure'                => 'F0FFFF',
            'beige'                => 'F5F5DC',
            'bisque'               => 'FFE4C4',
            'black'                => '000000',
            'blanchedalmond'       => 'FFEBCD',
            'blue'                 => '0000FF',
            'blueviolet'           => '8A2BE2',
            'brown'                => 'A52A2A',
            'burlywood'            => 'DEB887',
            'cadetblue'            => '5F9EA0',
            'chartreuse'           => '7FFF00',
            'chocolate'            => 'D2691E',
            'coral'                => 'FF7F50',
            'cornflowerblue'       => '6495ED',
            'cornsilk'             => 'FFF8DC',
            'crimson'              => 'DC143C',
            'cyan'                 => '00FFFF',
            'darkblue'             => '00008B',
            'darkcyan'             => '008B8B',
            'darkgoldenrod'        => 'B8860B',
            'darkgray'             => 'A9A9A9',
            'darkgreen'            => '006400',
            'darkgrey'             => 'A9A9A9',
            'darkkhaki'            => 'BDB76B',
            'darkmagenta'          => '8B008B',
            'darkolivegreen'       => '556B2F',
            'darkorange'           => 'FF8C00',
            'darkorchid'           => '9932CC',
            'darkred'              => '8B0000',
            'darksalmon'           => 'E9967A',
            'darkseagreen'         => '8FBC8F',
            'darkslateblue'        => '483D8B',
            'darkslategray'        => '2F4F4F',
            'darkslategrey'        => '2F4F4F',
            'darkturquoise'        => '00CED1',
            'darkviolet'           => '9400D3',
            'deeppink'             => 'FF1493',
            'deepskyblue'          => '00BFFF',
            'dimgray'              => '696969',
            'dimgrey'              => '696969',
            'dodgerblue'           => '1E90FF',
            'firebrick'            => 'B22222',
            'floralwhite'          => 'FFFAF0',
            'forestgreen'          => '228B22',
            'fuchsia'              => 'FF00FF',
            'gainsboro'            => 'DCDCDC',
            'ghostwhite'           => 'F8F8FF',
            'gold'                 => 'FFD700',
            'goldenrod'            => 'DAA520',
            'gray'                 => '808080',
            'green'                => '008000',
            'greenyellow'          => 'ADFF2F',
            'grey'                 => '808080',
            'honeydew'             => 'F0FFF0',
            'hotpink'              => 'FF69B4',
            'indianred'            => 'CD5C5C',
            'indigo'               => '4B0082',
            'ivory'                => 'FFFFF0',
            'khaki'                => 'F0E68C',
            'lavender'             => 'E6E6FA',
            'lavenderblush'        => 'FFF0F5',
            'lawngreen'            => '7CFC00',
            'lemonchiffon'         => 'FFFACD',
            'lightblue'            => 'ADD8E6',
            'lightcoral'           => 'F08080',
            'lightcyan'            => 'E0FFFF',
            'lightgoldenrodyellow' => 'FAFAD2',
            'lightgray'            => 'D3D3D3',
            'lightgreen'           => '90EE90',
            'lightgrey'            => 'D3D3D3',
            'lightpink'            => 'FFB6C1',
            'lightsalmon'          => 'FFA07A',
            'lightseagreen'        => '20B2AA',
            'lightskyblue'         => '87CEFA',
            'lightslategray'       => '778899',
            'lightslategrey'       => '778899',
            'lightsteelblue'       => 'B0C4DE',
            'lightyellow'          => 'FFFFE0',
            'lime'                 => '00FF00',
            'limegreen'            => '32CD32',
            'linen'                => 'FAF0E6',
            'magenta'              => 'FF00FF',
            'maroon'               => '800000',
            'mediumaquamarine'     => '66CDAA',
            'mediumblue'           => '0000CD',
            'mediumorchid'         => 'BA55D3',
            'mediumpurple'         => '9370D0',
            'mediumseagreen'       => '3CB371',
            'mediumslateblue'      => '7B68EE',
            'mediumspringgreen'    => '00FA9A',
            'mediumturquoise'      => '48D1CC',
            'mediumvioletred'      => 'C71585',
            'midnightblue'         => '191970',
            'mintcream'            => 'F5FFFA',
            'mistyrose'            => 'FFE4E1',
            'moccasin'             => 'FFE4B5',
            'navajowhite'          => 'FFDEAD',
            'navy'                 => '000080',
            'oldlace'              => 'FDF5E6',
            'olive'                => '808000',
            'olivedrab'            => '6B8E23',
            'orange'               => 'FFA500',
            'orangered'            => 'FF4500',
            'orchid'               => 'DA70D6',
            'palegoldenrod'        => 'EEE8AA',
            'palegreen'            => '98FB98',
            'paleturquoise'        => 'AFEEEE',
            'palevioletred'        => 'DB7093',
            'papayawhip'           => 'FFEFD5',
            'peachpuff'            => 'FFDAB9',
            'peru'                 => 'CD853F',
            'pink'                 => 'FFC0CB',
            'plum'                 => 'DDA0DD',
            'powderblue'           => 'B0E0E6',
            'purple'               => '800080',
            'red'                  => 'FF0000',
            'rosybrown'            => 'BC8F8F',
            'royalblue'            => '4169E1',
            'saddlebrown'          => '8B4513',
            'salmon'               => 'FA8072',
            'sandybrown'           => 'F4A460',
            'seagreen'             => '2E8B57',
            'seashell'             => 'FFF5EE',
            'sienna'               => 'A0522D',
            'silver'               => 'C0C0C0',
            'skyblue'              => '87CEEB',
            'slateblue'            => '6A5ACD',
            'slategray'            => '708090',
            'slategrey'            => '708090',
            'snow'                 => 'FFFAFA',
            'springgreen'          => '00FF7F',
            'steelblue'            => '4682B4',
            'tan'                  => 'D2B48C',
            'teal'                 => '008080',
            'thistle'              => 'D8BFD8',
            'tomato'               => 'FF6347',
            'turquoise'            => '40E0D0',
            'violet'               => 'EE82EE',
            'wheat'                => 'F5DEB3',
            'white'                => 'FFFFFF',
            'whitesmoke'           => 'F5F5F5',
            'yellow'               => 'FFFF00',
            'yellowgreen'          => '9ACD32',
            'transparent'          => 'FFFFFF00',
        );

    }

    protected function _parseFromHex($input)
    {
        if(substr($input, 0, 1) === '#') {
            return static::fromHex($input);
        }

        throw new \Exception('Error 2');
    }

    protected function _parseFromRgba($input)
    {
        $input = explode(',', str_replace([' ', 'rgba', '(', ')'], '', $input));
        if(count($input) < 3 || count($input) > 4) {
            throw new \Exception('Error rgba');
        }

        $input = array_map(function($value, $key) {
            if($key < 3) {
                if (!preg_match('/^\d+$/', $value = trim($value)) || $value < 0 || $value > 255) {
                    throw new \Exception('Error rgba num');
                }

                return (int)$value;
            } else {
                if (!is_numeric($value = trim($value)) || $value < 0 || $value > 1) {
                    throw new \Exception('Error rgba num');
                }
                return (float)$value;
            }
        }, $input, array_keys($input));

        return static::fromRgb($input);
    }

    protected function _parseFromRgb($input)
    {
        $input = explode(',', str_replace([' ', 'rgb', '(', ')'], '', $input));
        if(count($input) !== 3) {
            throw new \Exception('Error rgb');
        }

        $input = array_map(function($value) {
            if(!preg_match('/^\d+$/', $value = trim($value)) || $value < 0 || $value > 255) {
                throw new \Exception('Error rgb num');
            }

            return (int)$value;
        }, $input);

        return static::fromRgb($input);
    }

    protected function _parseFromHsl($input)
    {
        $input = explode(',', str_replace([' ', 'hsl', '(', ')', '%'], '', $input));
        if(count($input) !== 3) {
            throw new \Exception('Error hsl');
        }

        $input = array_map(function($value, $key) {
            $max = $key === 0 ? 360 : 100;
            if (!preg_match('/^\d+$/', $value = trim($value)) || $value < 0 || $value > $max) {
                throw new \Exception('Error hsl num ' . $value);
            }

            return (int)$value;
        }, $input, array_keys($input));

        return static::fromHsl($input);
    }

    protected function _parseFromHsla($input)
    {
        $input = explode(',', str_replace([' ', 'hsla', '(', ')', '%'], '', $input));
        if(count($input) < 3 || count($input) > 4) {
            throw new \Exception('Error hsla');
        }

        $input = array_map(function($value, $key) {
            $max = $key === 0 ? 360 : 100;
            if($key < 3) {
                if (!preg_match('/^\d+$/', $value = trim($value)) || $value < 0 || $value > $max) {
                    throw new \Exception('Error hsla num');
                }
                return (int)$value;
            } else {
                if (!is_numeric($value = trim($value)) || $value < 0 || $value > 1) {
                    throw new \Exception('Error hsla num');
                }
                return (float)$value;
            }
        }, $input, array_keys($input));

        return static::fromHsl($input);
    }
}
