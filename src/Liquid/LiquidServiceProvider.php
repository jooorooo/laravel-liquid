<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 14.2.2019 г.
 * Time: 08:29 ч.
 */

namespace Liquid;

use Illuminate\Support\ServiceProvider;

class LiquidServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->setupConfig();
    }

    public function boot()
    {
        $this->registerLiquidViewManager();
        $this->registerViewFinder();
        $this->registerView();
        $this->registerLiquidEngine();
        $this->registerLiquidEngineResover();
    }

    public function setupConfig()
    {
        $this->mergeConfigFrom($file = __DIR__ . '/../../config/liquid.php', 'liquid');

        $this->publishes([
            $file => config_path('liquid.php')
        ], 'config');

        $this->publishes([__DIR__ . '/../../migrations/' => database_path('migrations')], 'migrations');
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
            return new LiquidCompiler($app['liquid.view.manager']);
        });
    }

    /**
     * Register the Liquid engine implementation.
     *
     * @return void
     */
    public function registerLiquidViewManager()
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->app->singleton('liquid.view.manager', function ($app) {
            return new LiquidViewManager($app);
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
            $viewFinder->addExtension($app['config']['liquid.extension']);

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
        $oldView = [];
        if ($this->app->resolved('view')) {
            $oldView['dispatcher'] = $this->app['view']->getDispatcher();
            $oldView['container'] = $this->app['view']->getContainer();
            $oldView['shared'] = $this->app['view']->getShared();
        }

        $this->app->singleton('view', function ($app) use($oldView) {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $factory = new Factory($resolver, $finder, $oldView['dispatcher'] ?? $app['events']);

            $factory->addExtension($this->app['config']->get('liquid.extension'),
                'liquid');

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $factory->setContainer($oldView['container'] ?? $app);

            if(!empty($oldView['shared'])) {
                array_walk($oldView['shared'], function($value, $key) use ($factory) {
                    $factory->share($key, $value);
                });
            } else {
                $factory->share('app', $app);
            }

            return $factory;
        });
    }

}
