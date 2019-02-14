<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 14.2.2019 г.
 * Time: 08:48 ч.
 */

namespace Liquid;

use App\Common\Theme;
use Exception;
use Illuminate\Contracts\Cache\Repository;
use Throwable;
use ErrorException;
use Illuminate\View\ViewFinderInterface;
use Illuminate\Contracts\View\Engine;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class LiquidEngine implements Engine
{
    /**
     * @var Template
     */
    protected $_liquid;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];

    /**
     * LiquidEngine constructor.
     * @param ViewFinderInterface $fileFinder
     * @param Repository $cache
     * @param int $cacheExpire
     */
    public function __construct(ViewFinderInterface $fileFinder, Repository $cache, $cacheExpire = 60)
    {
        //Merge liqud config
        Liquid::$config = array_merge(Liquid::$config, config('liquid.liquid', []));

        $this->_liquid = new Template($fileFinder, $cache, $cacheExpire);
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array $data
     * @return string|null
     * @throws ErrorException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get($path, array $data = [])
    {
        $this->lastCompiled[] = $path;

        $obLevel = ob_get_level();
        try {
            $results = $this->_liquid->parse(file_get_contents($path))->render($data);
            array_pop($this->lastCompiled);
            return $results;
        } catch (Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e), $obLevel);
        }
        return null;
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception  $e
     * @param  int  $obLevel
     * @return void
     *
     * @throws $e
     */
    protected function handleViewException(Exception $e, $obLevel)
    {
        $e = new ErrorException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);

        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }

    /**
     * Get the exception message for an exception.
     *
     * @param  \Exception  $e
     * @return string
     */
    protected function getMessage(Exception $e)
    {
        return $e->getMessage().' (View: '.realpath(last($this->lastCompiled)).')';
    }
}