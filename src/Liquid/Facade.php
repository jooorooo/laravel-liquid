<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 08:50 ч.
 */

namespace Liquid;

use Illuminate\Support\Facades\Facade AS IlluminateFacade;
use Illuminate\View\ViewFinderInterface;

/**
 * @method static void compile($path = null)
 * @method static string findTemplate($path)
 * @method static bool getAutoEscape()
 * @method static string getCompiledPath()
 * @method static string getFileSource()
 * @method static string getPath()
 * @method static array getTags()
 * @method static string getTemplateSource()
 * @method static ViewFinderInterface getViewFinder()
 * @method static bool isExpired()
 * @method static LiquidCompiler registerFilter($filter)
 * @method static LiquidCompiler registerTag($name, $class)
 * @method static string render($path, array $assigns = array())
 * @method static LiquidCompiler setAutoEscape($value)
 * @method static LiquidCompiler setExtension($value)
 * @method static LiquidCompiler setFilters(array $filters)
 * @method static LiquidCompiler setPath($path)
 * @method static LiquidCompiler setTags(array $tags)
 * @method static array tokenize($source)
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
        return 'liquid.compiler';
    }
}
