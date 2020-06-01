<?php

namespace Liquid;

use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use Liquid\ViewFinder\DatabaseViewFinder;

class LiquidViewFinder
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
        dd($this->store('database'));
    }

    /**
     * Get a cache store instance by name, wrapped in a repository.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Cache\Repository
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
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given store.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Cache\Repository
     *
     * @throws InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Liquid view finder [{$name}] is not defined.");
        }

        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    /**
     * Create an instance of the array cache driver.
     *
     * @return FileViewFinder
     */
    protected function createFileDriver()
    {
        return $this->app['view']->getFinder();
    }

    /**
     * Create an instance of the array cache driver.
     *
     * @return DatabaseViewFinder
     */
    protected function createDatabaseDriver()
    {
        return new DatabaseViewFinder($this->app['view']->getFinder(), $this->getConfig('database'));
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['liquid.loader'];
    }

    /**
     * Get the cache connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["liquid.loader_stores.{$name}"];
    }
}
