<?php

namespace Liquid\ViewFinders;

use Illuminate\Database\ConnectionInterface;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use Liquid\TemplateContent;

class DatabaseViewFinder implements ViewFinderInterface
{
    /**
     * The filesystem instance.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * The array of active view tables.
     *
     * @var array
     */
    protected $tables = [];

    /**
     * The array of views that have been located.
     *
     * @var array
     */
    protected $views = [];

    /**
     * The namespace to file table hints.
     *
     * @var array
     */
    protected $hints = [];

    /**
     * Register a view extension with the finder.
     *
     * @var array
     */
    protected $extensions = ['liquid'];

    /**
     * Create a new file view loader instance.
     *
     * @param  ConnectionInterface $connection
     * @param  string $table
     * @param  array  $extensions
     * @return void
     */
    public function __construct(ConnectionInterface $connection, $table, array $extensions = null)
    {
        $this->connection = $connection;
        $this->tables = is_array($table) ? $table : [$table];

        if (isset($extensions)) {
            $this->extensions = $extensions;
        }
    }

    /**
     * Get the fully qualified location of the view.
     *
     * @param  string  $name
     * @return TemplateContent
     */
    public function find($name)
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        if ($this->hasHintInformation($name = trim($name))) {
            return $this->views[$name] = $this->findNamespacedView($name);
        }

        return $this->views[$name] = $this->findInTables($name, $this->tables);
    }

    /**
     * Get the table to a template with a named table.
     *
     * @param  string  $name
     * @return TemplateContent
     */
    protected function findNamespacedView($name)
    {
        [$namespace, $view] = $this->parseNamespaceSegments($name);

        return $this->findInTables($view, $this->hints[$namespace]);
    }

    /**
     * Get the segments of a template with a named table.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseNamespaceSegments($name)
    {
        $segments = explode(static::HINT_PATH_DELIMITER, $name);

        if (count($segments) !== 2) {
            throw new InvalidArgumentException("View [{$name}] has an invalid name.");
        }

        if (! isset($this->hints[$segments[0]])) {
            throw new InvalidArgumentException("No hint table defined for [{$segments[0]}].");
        }

        return $segments;
    }

    /**
     * Find the given view in the list of tables.
     *
     * @param  string  $name
     * @param  array   $tables
     * @return TemplateContent
     *
     * @throws \InvalidArgumentException
     */
    protected function findInTables($name, $tables)
    {
        foreach ((array) $tables as $table) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($template = $this->table($table)->where('path', $file)->first()) {
                    return new TemplateContent($template->content, strtotime($template->updated_at), $table . '::' . $file, $name);
                }
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    /**
     * Get an array of possible view files.
     *
     * @param  string  $name
     * @return array
     */
    protected function getPossibleViewFiles($name)
    {
        return array_map(function ($extension) use ($name) {
            return str_replace('.', '/', $name).'.'.$extension;
        }, $this->extensions);
    }

    /**
     * Add a table to the finder.
     *
     * @param  string  $location
     * @return void
     */
    public function addLocation($location)
    {
        $this->tables[] = $location;
    }

    /**
     * Prepend a table to the finder.
     *
     * @param  string  $location
     * @return void
     */
    public function prependLocation($location)
    {
        array_unshift($this->tables, $location);
    }

    /**
     * Add a namespace hint to the finder.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return void
     */
    public function addNamespace($namespace, $hints)
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($this->hints[$namespace], $hints);
        }

        $this->hints[$namespace] = $hints;
    }

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return void
     */
    public function prependNamespace($namespace, $hints)
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($hints, $this->hints[$namespace]);
        }

        $this->hints[$namespace] = $hints;
    }

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return void
     */
    public function replaceNamespace($namespace, $hints)
    {
        $this->hints[$namespace] = (array) $hints;
    }

    /**
     * Register an extension with the view finder.
     *
     * @param  string  $extension
     * @return void
     */
    public function addExtension($extension)
    {
        if (($index = array_search($extension, $this->extensions)) !== false) {
            unset($this->extensions[$index]);
        }

        array_unshift($this->extensions, $extension);
    }

    /**
     * Returns whether or not the view name has any hint information.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasHintInformation($name)
    {
        return strpos($name, static::HINT_PATH_DELIMITER) > 0;
    }

    /**
     * Flush the cache of located views.
     *
     * @return void
     */
    public function flush()
    {
        $this->views = [];
    }

    /**
     * Get the underlying database connection.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the active view tables.
     *
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Get the namespace to file table hints.
     *
     * @return array
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * Get registered extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get a query builder for the cache table.
     *
     * @param string $table
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table($table)
    {
        return $this->connection->table($table);
    }
}
