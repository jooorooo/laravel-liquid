<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 08:50 ч.
 */

namespace Liquid;

use Illuminate\Support\Facades\Facade AS IlluminateFacade;

/**
 * @method static void registerTag($name, $class)
 * @method static Document getRoot()
 * @method static array getTags()
 * @method static void registerFilter($filter)
 * @method static LiquidEngine parse($source)
 * @method static string render(array $assigns = array(), $filters = null, array $registers = array())
 * @method static null|string get($path, array $data = [])
 * @method static array arrayFlatten($array)
 */

class Facade extends IlluminateFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return static::$app['view']->getEngineResolver()->resolve('liquid');
    }
}