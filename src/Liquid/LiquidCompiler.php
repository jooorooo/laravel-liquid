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

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use Liquid\Loader\FileContent;
use Liquid\Traits\TokenizeTrait;
use ReflectionException;

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
     * @var FileContent
     */
    protected $path;

    /**
     * @var array
     */
    protected $filemtime = [];

    /**
     * @var CompilerEngine
     */
    protected $compiler;

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

    // Variable name.
    const VARIABLE_NAME = '[a-zA-Z_][a-zA-Z_0-9.-]*';

    const QUOTED_FRAGMENT = '"[^"]*"|\'[^\']*\'|(?:[^\s,\|\'"]|"[^"]*"|\'[^\']*\')+';

    const TAG_ATTRIBUTES = '/(\w+)\s*\:\s*(' . self::QUOTED_FRAGMENT . ')/';

    /**
     * Create a new compiler instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the path currently being compiled.
     *
     * @return FileContent
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
        if($class instanceof Closure) {
            throw new InvalidArgumentException('Type "Closure" is not allowed for tag!');
        }
        $this->tags[$name] = $class;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags ? : [];
    }

    /**
     * Register the filter
     *
     * @param string $filter
     * @return LiquidCompiler
     */
    public function registerFilter($filter)
    {
        if($filter instanceof Closure) {
            throw new InvalidArgumentException('Type "Closure" is not allowed for filter!');
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
     * @param $template
     * @return string
     * @throws FileNotFoundException
     */
    public function getFileSource($template)
    {
        /** @var FileContent $path */
        $path = $this->getLiquidViewLoader()->store()->find($template);
        if(!array_key_exists($path->getPath(), $this->filemtime)) {
            $this->filemtime[$path->getPath()] = $path->getFileMTime();
        }

        return $path->getContent();
    }

    /**
     * @param $path
     * @return void
     */
    public function setFileMtime($path)
    {
        if($path instanceof FileContent && !array_key_exists($path->getPath(), $this->filemtime) && $path->getFileMTime()) {
            $this->filemtime[$path->getPath()] = $path->getFileMTime();
        }
    }

    /**
     * @param $path
     * @return string
     * @throws FileNotFoundException
     */
    public function getTemplateSource($path)
    {
        return $this->getFileSource($path);
    }

    /**
     * @inheritDoc
     */
    public function compile($path)
    {

        if(!($path instanceof FileContent)) {
            /** @var FileContent $path */
            $path = $this->getLiquidViewLoader()->store()->find($path);
        }

        $this->setPath($path);

        $templateTokens = $this->tokenize( $path->getContent() );

        $this->root = new Document(null, $templateTokens, $this);

        if($this->isExpired($path)) {
            $this->getCacheStore()->forever($this->getCompiledKey($path), (object)[
                'filemtime' => time(),
                'content' => $this->root
            ]);
        }
    }

    /**
     * Get the path to the compiled version of a view.
     *
     * @param  FileContent  $path
     * @return string
     */
    public function getCompiledKey($path)
    {
        return sha1($path->getPath());
    }

    /**
     * Renders the current template
     *
     * @param FileContent $path
     * @param array $assigns an array of values for the template
     *
     * @return string
     * @throws LiquidException
     * @throws ReflectionException
     */
    public function render($path, array $assigns = [])
    {
        $context = new Context($assigns);

        if($this->filters) {
            foreach ($this->filters as $filter) {
                $context->addFilter($filter);
            }
        }

        $this->root = $this->getCacheStore()->get($this->getCompiledKey($path))->content;

        return $this->root->render($context);
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @param  FileContent  $path
     * @return bool
     */
    public function isExpired($path)
    {
        $compiled = $this->getCompiledKey($path);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (! $this->getCacheStore()->has($compiled)) {
            return true;
        }

        $compiledData = $this->getCacheStore()->get($compiled);
        if(empty($compiledData->filemtime) || !isset($compiledData->content)) {
            return true;
        }

        $pathLastModify = count($this->filemtime) > 0 ? max($this->filemtime) : $path->getFileMTime();

        return $pathLastModify >= $compiledData->filemtime;
    }

    public function getTextLine($text)
    {
        $pattern = '/' . preg_quote($text, '/') . '/i';
        $lineNumber = 0;
        if ($this->getPath() && preg_match($pattern, $content = $this->getFileSource($this->getPath()), $matches, PREG_OFFSET_CAPTURE)) {
            //PREG_OFFSET_CAPTURE will add offset of the found string to the array of matches
            //now get a substring of the offset length and explode it by \n
            $lineNumber = count(explode(PHP_EOL, substr($content, 0, $matches[0][1])));
        }

        return $lineNumber;
    }

    /**
     * @return Repository|\Illuminate\Contracts\Cache\Repository
     */
    public function getCacheStore()
    {
        return cache()->store(config('liquid.compiled_store', 'file'));
    }

    /**
     * @return LiquidViewManager
     */
    public function getLiquidViewLoader()
    {
        return app('liquid.view.manager');
    }
}
