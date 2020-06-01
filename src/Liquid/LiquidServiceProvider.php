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
        $this->registerLiquidEngine();
        $this->registerLiquidEngineResolver();
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
    public function registerLiquidViewFinder()
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->app->singleton('liquid.view.finder', function () {
            return new LiquidViewFinder($this->app);
        });
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
        $this->app->singleton('liquid.compiler', function () {
            return new LiquidCompiler(
                $this->app['files'], $this->app['config']['view.compiled']
            );
        });
    }

    /**
     * Register the Liquid engine implementation.
     *
     * @return void
     */
    public function registerLiquidEngineResolver()
    {
        $this->app['view.engine.resolver']->register('liquid', function () {
            return new CompilerEngine($this->app['liquid.compiler'], $this->app['config']['liquid'] ?? []);
        });
    }

    /**
     * Register the Liquid extension.
     *
     * @return void
     */
    public function registerLiquidExtension()
    {
        $this->app['view']->addExtension(
            $this->app['config']->get('liquid.extension'),
            'liquid'
        );
    }

}
