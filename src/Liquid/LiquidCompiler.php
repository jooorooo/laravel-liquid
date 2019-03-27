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
use Illuminate\Support\Facades\View;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\View\ViewFinderInterface;
use Liquid\Traits\TokenizeTrait;

/**
 * The Template class.
 *
 * http://cheat.markdunkley.com/
 * https://stackoverflow.com/questions/29123188/enabling-liquid-templating-syntax-highlight-in-webstorm-phpstorm/29337624#29337624
 * https://github.com/Shopify/liquid
 *
 */
class LiquidCompiler extends Compiler implements CompilerInterface
{

    use TokenizeTrait;

    /**
     * @var Document The root of the node tree
     */
    protected $root;

    /**
     * @var array Globally included filters
     */
    protected $filters = array();

    /**
     * @var array Custom tags
     */
    protected $tags = [];

    /**
     * @var bool $auto_escape
     */
    protected $auto_escape = true;

    /**
     * @var string
     */
    protected $path;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];

    // Operations tags.
    const OPERATION_TAGS = ['{%', '%}'];

    // Variable tags.
    const VARIABLE_TAG = ['{{', '}}'];

    const ANY_STARTING_TAG = self::VARIABLE_TAG[0] . '|' . self::OPERATION_TAGS[0];

    const PARTIAL_TEMPLATE_PARSER = self::VARIABLE_TAG[0] . '.*?' . self::VARIABLE_TAG[1] . '|' . self::OPERATION_TAGS[0] . '.*?' . self::OPERATION_TAGS[1];

    const TEMPLATE_PARSER = self::PARTIAL_TEMPLATE_PARSER . '|' . self::ANY_STARTING_TAG;

    // Variable name.
    const VARIABLE_NAME = '[a-zA-Z_][a-zA-Z_0-9.-]*';

    const QUOTED_FRAGMENT = '"[^"]*"|\'[^\']*\'|(?:[^\s,\|\'"]|"[^"]*"|\'[^\']*\')+';

    const QUOTED_FRAGMENT_FILTER_ARGUMENT = '"[^":]*"|\'[^\':]*\'|(?:[^\s:,\|\'"]|"[^":]*"|\'[^\':]*\')+';

    const TAG_ATTRIBUTES = '/(\w+)\s*\:\s*(' . self::QUOTED_FRAGMENT . ')/';
    
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
     * @return LiquidCompiler
     */
    public function setExtension($value)
    {
        app('view')->getFinder()->addExtension($value);
        $this->getViewFinder()->addExtension($value);
        return $this;
    }

    /**
     * @return ViewFinderInterface
     */
    public function getViewFinder()
    {
        return View::shared('__env')->getFinder();
    }

    /**
     * @return bool
     */
    public function getAutoEscape()
    {
        return $this->auto_escape;
    }

    /**
     * Set tags
     *
     * @param bool $value
     * @return LiquidCompiler
     */
    public function setAutoEscape($value)
    {
        $this->auto_escape = $value;
        return $this;
    }

    /**
     * Set tags
     *
     * @param array $tags
     * @return LiquidCompiler
     */
    public function setTags(array $tags)
    {
        foreach($tags AS $key => $value) {
            $this->registerTag($key, $value);
        }
        return $this;
    }

    /**
     * Register custom Tags
     *
     * @param string $name
     * @param string $class
     * @return LiquidCompiler
     */
    public function registerTag($name, $class)
    {
        if($class instanceof \Closure) {
            throw new \InvalidArgumentException('Type "Closure" is not allowed for tag!');
        }
        $this->tags[$name] = $class;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Register the filter
     *
     * @param string $filter
     * @return LiquidCompiler
     */
    public function registerFilter($filter)
    {
        if($filter instanceof \Closure) {
            throw new \InvalidArgumentException('Type "Closure" is not allowed for filter!');
        }

        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Set the filters
     *
     * @param array $filters
     * @return LiquidCompiler
     */
    public function setFilters(array $filters)
    {
        array_map([$this, 'registerFilter'], $filters);
        return $this;
    }

    /**
     * @param $path
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getFileSource($path)
    {
        return $this->files->get($path);
    }

    /**
     * @param $path
     * @return string
     */
    public function findTemplate($path)
    {
        return $this->getViewFinder()->find($path);
    }

    /**
     * @param $path
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getTemplateSource($path)
    {
        return $this->getFileSource($this->findTemplate($path));
    }

    /**
     * Compile the view at the given path.
     *
     * @param  string $path
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }

        $templateTokens = $this->tokenize($this->getFileSource($this->getPath()));

        $this->root = new Document(null, $templateTokens, $this);

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
    public function render($path, array $assigns = [])
    {
        $context = new Context($assigns);

        if($this->filters) {
            foreach ($this->filters as $filter) {
                $context->addFilter($filter);
            }
        }

        if(is_null($this->root)) {
            $this->root = unserialize($this->getFileSource($this->getCompiledPath($path)));
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
