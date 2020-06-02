<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 14.2.2019 г.
 * Time: 08:29 ч.
 */

namespace Liquid;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class LiquidServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->setupConfig();
    }

    public function boot()
    {
        $this->registerViewFinder();
        $this->registerView();
        $this->registerLiquidEngine();
        $this->registerLiquidEngineResover();
        $this->registerLiquidExtension();
    }

    public function setupConfig()
    {
        $this->mergeConfigFrom($file = __DIR__ . '/../../config/liquid.php', 'liquid');

        $this->publishes([
            $file => config_path('liquid.php')
        ], 'config');
    }

    /**
     * Register the Liquid engine implementation.
     *
     * @return void
     */
    public function registerLiquidEngine()
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->app->singleton('liquid.compiler', function ($app) {
            return new LiquidCompiler($app['files']);
        });
    }

    /**
     * Register the Liquid engine implementation.
     *
     * @return void
     */
    public function registerLiquidEngineResover()
    {
        $this->app['view.engine.resolver']->register('liquid', function () {
            return new CompilerEngine($this->app['liquid.compiler'], $this->app['config']->get('liquid', []));
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $oldFinder = [];
        if ($this->app->resolved('view.finder')) {
            $oldFinder['paths'] = $this->app['view']->getFinder()->getPaths();
            $oldFinder['hints'] = $this->app['view']->getFinder()->getHints();
            $oldFinder['extensions'] = $this->app['view']->getFinder()->getExtensions();
        }

        $this->app->bind('view.finder', function ($app) use ($oldFinder) {

            $paths = (isset($oldFinder['paths']))?array_unique(array_merge($app['config']['view.paths'] ?? [], $oldFinder['paths']), SORT_REGULAR):$app['config']['view.paths'];

            $viewFinder = new FileViewFinder($app['files'], $paths, $oldFinder['extensions'] ?? null);
            if (!empty($oldFinder['hints'])) {
                array_walk($oldFinder['hints'], function($value, $key) use ($viewFinder) {
                    $viewFinder->addNamespace($key, $value);
                });
            }

            return $viewFinder;
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerView()
    {
        if ($this->app->resolved('view')) {
            $this->app['view']->setFinder($this->app['view.finder']);
        }
    }

    public function registerLiquidExtension()
    {
        $this->app['view']->addExtension(
            $this->app['config']->get('liquid.extension'),
            'liquid'
        );
    }

}
