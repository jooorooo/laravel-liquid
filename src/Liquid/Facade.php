<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 21.3.2019 г.
 * Time: 08:50 ч.
 */

namespace Liquid;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Facade AS IlluminateFacade;
use Illuminate\View\ViewFinderInterface;

/**
 * @method static View make($view, $data = [], $mergeData = [])
 * @method static View first($views, $data = [], $mergeData = [])
 * @method static string renderWhen($condition, $view, $data = [], $mergeData = [])
 * @method static string renderEach($view, $data, $iterator, $empty = 'raw|')
 * @method static bool exists($view)
 * @method static mixed share($key, $value = null)
 * @method static void addLocation($location)
 * @method static Factory addNamespace($namespace, $hints)
 * @method static Factory prependNamespace($namespace, $hints)
 * @method static Factory replaceNamespace($namespace, $hints)
 * @method static CompilerEngine getEngine()
 * @method static LiquidCompiler getCompiler()
 * @method static ViewFinderManager getFinder()
 * @method static void flushFinderCache()
 * @method static Dispatcher getDispatcher()
 * @method static void setDispatcher(Dispatcher $events)
 * @method static Container getContainer()
 * @method static void setContainer(Container $container)
 * @method static mixed shared($key, $default = null)
 * @method static array getShared()
 * @method static void macro($name, $macro)
 * @method static void mixin($mixin)
 * @method static bool hasMacro($name)
 * @method static array creator($views, $callback)
 * @method static array composers($composers)
 * @method static array composer($views, $callback)
 * @method static void callComposer($view)
 * @method static void callCreator($view)
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
        return 'liquid.factory';
    }
}
