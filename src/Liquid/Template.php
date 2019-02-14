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

use Illuminate\Contracts\Cache\Repository;
use Illuminate\View\ViewFinderInterface;

/**
 * The Template class.
 *
 * Example:
 *
 *     $tpl = new \Liquid\Template();
 *     $tpl->parse(template_source);
 *     $tpl->render(array('foo'=>1, 'bar'=>2);
 */
class Template
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
     * @var Repository
     */
    private static $cache;

    /**
     * @var integer
     */
    private static $cache_expire = 3600;

    /**
     * Constructor.
     *
     * @param ViewFinderInterface $viewFinder
     * @param Repository $cache
     * @param int $cache_expire
     */
    public function __construct(ViewFinderInterface $viewFinder, Repository $cache, int $cache_expire = 60)
    {
        $this->viewFinder = $viewFinder;
        $this->setCache($cache);
        $this->setCacheExpire($cache_expire);
    }

    /**
     * @param ViewFinderInterface $viewFinder
     * @return Template
     */
    public function setFileSystem(ViewFinderInterface $viewFinder)
    {
        $this->viewFinder = $viewFinder;
        return $this;
    }

    /**
     * @param Repository $cache
     * @return Template
     */
    public function setCache(Repository $cache)
    {
        self::$cache = $cache;
        return $this;
    }

    /**
     * @param int $expire
     * @return Template
     */
    public function setCacheExpire(int $expire)
    {
        self::$cache_expire = $expire;
        return $this;
    }

    /**
     * @return Repository
     */
    public static function getCache()
    {
        return self::$cache;
    }

    /**
     * @return integer
     */
    public static function getCacheExpire()
    {
        return self::$cache_expire;
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
     * @return Template
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function parse($source)
    {
        if ($cache = self::getCache()) {
            if (($this->root = $cache->get(md5($source))) != false && $this->root->checkIncludes() != true) {
            } else {
                $tokens = Template::tokenize($source);
                $this->root = new Document($tokens, $this->viewFinder);
                $cache->set(md5($source), $this->root, self::getCacheExpire());
            }
        } else {
            $tokens = Template::tokenize($source);
            $this->root = new Document($tokens, $this->viewFinder);
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
}
