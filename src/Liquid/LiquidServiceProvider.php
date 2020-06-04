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
        $this->registerLiquidViewFinder();
        $this->registerLiquidCompiler();
        $this->registerLiquidFactory();
        $this->registerLiquidEngine();
    }

    public function setupConfig()
    {
        $this->mergeConfigFrom($file = __DIR__ . '/../../config/liquid.php', 'liquid');

        $this->publishes([
            $file => config_path('liquid.php')
        ], 'config');
    }

    /**
     * Register the Liquid view finder.
     *
     * @return void
     */
    public function registerLiquidViewFinder()
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->app->singleton('liquid.view.finder', function ($app) {
            return new ViewFinderManager($app);
        });
    }

    /**
     * Register the Liquid engine implementation.
     *
     * @return void
     */
    public function registerLiquidCompiler()
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->app->singleton('liquid.compiler', function () {
            return new LiquidCompiler();
        });
    }

    /**
     * Register the Liquid engine implementation.
     *
     * @return void
     */
    public function registerLiquidFactory()
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->app->singleton('liquid.factory', function () {
            return new Factory(
                $this->app['liquid.compiler'],
                $this->app['liquid.view.finder'],
                $this->app['liquid.engine'],
                $this->app['events']
            );
        });
    }

    /**
     * Register the Liquid engine implementation.
     *
     * @return void
     */
    public function registerLiquidEngine()
    {
        $this->app->singleton('liquid.engine', function () {
            return new CompilerEngine($this->app['liquid.compiler'], $this->app['config']->get('liquid', []));
        });
    }

}
