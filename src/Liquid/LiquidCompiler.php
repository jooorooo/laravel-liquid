<?php

/**
 * JS: https://github.com/harttle/liquidjs
 *
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use ErrorException;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use Liquid\Traits\TokenizeTrait;

/**
 * The Template class.
 *
 * http://cheat.markdunkley.com/
 *
 */
class LiquidCompiler extends Compiler implements CompilerInterface
{

    use TokenizeTrait;

    /**
     * @var Document The root of the node tree
     */
    private $root;

    /**
     * @var array Globally included filters
     */
    private $filters = array();

    /**
     * @var array Custom tags
     */
    private static $tags = [];

    /**
     * @var bool $auto_escape
     */
    private static $auto_escape = true;

    /**
     * @var string
     */
    private $path;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];

    // Separator between filters.
    const FILTER_SEPARATOR = '\|';

    // Operations tags.
    const OPERATION_TAGS = ['{%', '%}'];

    // Variable tags.
    const VARIABLE_TAG = ['{{', '}}'];

    // Variable name.
    const VARIABLE_NAME = '[a-zA-Z_][a-zA-Z_0-9.-]*';

    const QUOTED_FRAGMENT = '"[^"]*"|\'[^\']*\'|(?:[^\s,\|\'"]|"[^"]*"|\'[^\']*\')+';

    const QUOTED_FRAGMENT_FILTER_ARGUMENT = '"[^":]*"|\'[^\':]*\'|(?:[^\s:,\|\'"]|"[^":]*"|\'[^\':]*\')+';

    const TAG_ATTRIBUTES = '/(\w+)\s*\:\s*(' . self::QUOTED_FRAGMENT . ')/';

    const TOKENIZATION_REGEXP = '/(' . self::OPERATION_TAGS[0] . '.*?' . self::OPERATION_TAGS[1] . '|' . self::VARIABLE_TAG[0] . '.*?' . self::VARIABLE_TAG[1] . ')/';

    // Separator for arguments.
    const ARGUMENT_SEPARATOR = ',';

    // Separator for argument names and values.
    const FILTER_ARGUMENT_SEPARATOR = ':';

    /**
     * Get the path currently being compiled.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path currently being compiled.
     *
     * @param  string  $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Set view extension
     *
     * @param string $value
     */
    public function setExtension($value)
    {
        app('view')->getFinder()->addExtension($value);
    }

    /**
     * @return bool
     */
    public static function getAutoEscape()
    {
        return self::$auto_escape;
    }

    /**
     * Set tags
     *
     * @param bool $value
     */
    public static function setAutoEscape($value)
    {
        self::$auto_escape = $value;
    }

    /**
     * @return Document
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set tags
     *
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        self::$tags = $tags;
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
     * Set the filters
     *
     * @param array $filters
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Compile the view at the given path.
     *
     * @param  string $path
     * @return void
     * @throws LiquidException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \ReflectionException
     */
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }

        $source = $this->files->get($path);

        $templateTokens = $this->tokenize($source);

        $this->root = new Document(null, $templateTokens, $this->files);

        $this->files->put($this->getCompiledPath($this->getPath()), serialize($this->root));
    }

    /**
     * Renders the current template
     *
     * @param string $path
     * @param array $assigns an array of values for the template
     *
     * @return string
     * @throws LiquidException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \ReflectionException
     */
    public function render($path, array $assigns = array())
    {
        $context = new Context($assigns);

        if($this->filters) {
            foreach ($this->filters as $filter) {
                $context->addFilters($filter);
            }
        }

        if(is_null($this->root)) {
            $this->root = unserialize($this->files->get($this->getCompiledPath($path)));
        }

        return $this->root->render($context);
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception $e
     * @param  int $obLevel
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
     * @param  \Exception $e
     * @return string
     */
    protected function getMessage(\Exception $e)
    {
        return $e->getMessage() . ' (View: ' . realpath(last($this->lastCompiled)) . ')';
    }
}
