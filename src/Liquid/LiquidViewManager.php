<?php

namespace Liquid;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use Liquid\ViewFinder\FileFinder;
use Liquid\ViewFinder\MySqlFinder;

class LiquidViewManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved cache stores.
     *
     * @var array
     */
    protected $stores = [];

    /**
     * Create a new Cache manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a cache store instance by name, wrapped in a repository.
     *
     * @param  string|null  $name
     * @return FileViewFinder
     */
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->stores[$name] = $this->get($name);
    }

    /**
     * Attempt to get the store from the local cache.
     *
     * @param  string  $name
     * @return FileViewFinder
     */
    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given store.
     *
     * @param  string  $name
     * @return FileViewFinder
     *
     * @throws InvalidArgumentException
     */
    protected function resolve($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException("View finder connection [$name] for liquid is not defined.");
        }

        $driverMethod = 'create'.ucfirst($name).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($this->app);
        } else {
            throw new InvalidArgumentException("Driver [{$name}] is not supported.");
        }
    }

    protected function createFileDriver(Application $app)
    {
        return new FileFinder($app['files'], $app['view.finder']->getPaths(), array_unique(array_merge([$app['config']['liquid.extension']], $app['view.finder']->getExtensions())));
    }

    protected function createMysqlDriver(Application $app)
    {
        return new MySqlFinder(
            $app['db']->connection($this->app['config']['liquid.view_store.connection']),
            $this->app['config']['liquid.view_store.table']
        );
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['liquid.view_store.connection'];
    }

}
