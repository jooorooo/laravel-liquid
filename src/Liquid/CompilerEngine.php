<?php

namespace Liquid;

use Exception;
use ErrorException;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\View\Engines\PhpEngine;
use Throwable;

class CompilerEngine extends PhpEngine
{
    /**
     * The Blade compiler instance.
     *
     * @var LiquidCompiler
     */
    protected $compiler;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];

    /**
     * Create a new Blade view engine instance.
     *
     * @param CompilerInterface $compiler
     * @param array $config
     */
    public function __construct(CompilerInterface $compiler, array $config = [])
    {
        $this->compiler = $compiler;

        foreach($config AS $key => $value) {
            if(method_exists($this->compiler, $method = camel_case('set_' . $key))) {
                $this->compiler->$method($value);
            }
        }
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param TemplateContent $path
     * @param array $data
     * @return string|null
     * @throws ErrorException
     */
    public function get($path, array $data = [])
    {
        $this->lastCompiled[] = $path;

        $obLevel = ob_get_level();
        try {
            $this->compiler->compile($path);

            $results = $this->compiler->render($path, $data);

            array_pop($this->lastCompiled);

            return $results;
        } catch (Throwable $e) {
            $this->handleViewException(new Exception($e->getMessage(), $e->getCode(), $e), $obLevel);
        }
        return null;
    }

    /**
     * Handle a view exception.
     *
     * @param  Exception $e
     * @param  int $obLevel
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
     * @param  Exception $e
     * @return string
     */
    protected function getMessage(Exception $e)
    {
        return $e->getMessage() . ' (View: ' . realpath(last($this->lastCompiled)->getPath()) . ')';
    }
}
