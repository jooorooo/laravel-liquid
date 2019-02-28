<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use ErrorException;
use Illuminate\Contracts\View\Engine;
use Illuminate\View\ViewFinderInterface;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * The Template class.
 *
 * Example:
 *
 *     $tpl = new \Liquid\LiquidEngine();
 *     $tpl->parse(template_source);
 *     $tpl->render(array('foo'=>1, 'bar'=>2);
 */
class LiquidEngine implements Engine
{
    /**
     * @var Document The root of the node tree
     */
    private $root;

    /**
     * @var ViewFinderInterface The file system to use for includes
     */
    protected $viewFinder;

    /**
     * @var array Globally included filters
     */
    private $filters = array();

    /**
     * @var array Custom tags
     */
    private static $tags = array();

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var string
     */
    private $compiled;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];


    // Separator between filters.
    const FILTER_SEPARATOR = '\|';

    // Separator for arguments.
    const ARGUMENT_SEPARATOR = ',';

    // Separator for argument names and values.
    const FILTER_ARGUMENT_SEPARATOR = ':';

    /**
     * Constructor.
     *
     * @param ViewFinderInterface $viewFinder
     * @param Filesystem $files
     * @param $compiled
     */
    public function __construct(ViewFinderInterface $viewFinder, Filesystem $files, $compiled)
    {
        $this->viewFinder = $viewFinder;
        $this->files = $files;
        $this->compiled = $compiled;
    }

    /**
     * @return Document
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Register custom Tags
     *
     * @param string $name
     * @param string $class
     */
    public function registerTag($name, $class)
    {
        self::$tags[$name] = $class;
    }

    /**
     * @return array
     */
    public static function getTags()
    {
        return self::$tags;
    }

    /**
     * Register the filter
     *
     * @param string $filter
     */
    public function registerFilter($filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Tokenizes the given source string
     *
     * @param string $source
     *
     * @return array
     */
    public static function tokenize($source)
    {
        return empty($source)
            ? array()
            : preg_split(Liquid::get('TOKENIZATION_REGEXP'), $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * Parses the given source string
     *
     * @param string $source
     *
     * @return LiquidEngine
     * @throws LiquidException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function parse($source)
    {
        $file = md5($source) . '.liquid';
        $path = $this->compiled . '/' . $file;

        if(!$this->files->exists($path) || !($this->root = @unserialize($this->files->get($path))) || !($this->root->checkIncludes() != true)) {
            $templateTokens = self::tokenize($source);
            $this->root = new Document($templateTokens, $this->viewFinder, $this->files, $this->compiled);
            $this->files->put($path, serialize($this->root));
        }

        return $this;
    }

    /**
     * Renders the current template
     *
     * @param array $assigns an array of values for the template
     * @param array $filters additional filters for the template
     * @param array $registers additional registers for the template
     *
     * @return string
     * @throws LiquidException
     */
    public function render(array $assigns = array(), $filters = null, array $registers = array())
    {
        $context = new Context($assigns, $registers);

        if (!is_null($filters)) {
            if (is_array($filters)) {
                $this->filters = array_merge($this->filters, $filters);
            } else {
                $this->filters[] = $filters;
            }
        }

        foreach ($this->filters as $filter) {
            $context->addFilters($filter);
        }

        return $this->root->render($context);
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array $data
     * @return string|null
     * @throws ErrorException
     */
    public function get($path, array $data = [])
    {
        $this->lastCompiled[] = $path;

        $obLevel = ob_get_level();
        try {
            $results = $this->parse(file_get_contents($path))->render($data);
            array_pop($this->lastCompiled);
            return $results;
        } catch (\Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (\Throwable $e) {
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
    protected function handleViewException(\Exception $e, $obLevel)
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
    protected function getMessage(\Exception $e)
    {
        return $e->getMessage().' (View: '.realpath(last($this->lastCompiled)).')';
    }
}
