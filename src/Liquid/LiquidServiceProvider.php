<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 14.2.2019 г.
 * Time: 08:29 ч.
 */

namespace Liquid;

use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider;

class LiquidServiceProvider extends ViewServiceProvider
{

    public function register()
    {
        parent::register();
        $this->setupConfig();

        $this->registerLiquidEngine($this->app['view.engine.resolver']);

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
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->singleton('view.finder', function ($app) {
            $finder = new FileViewFinder($app['files'], $app['config']['view.paths']);
            $finder->addExtension($this->app['config']->get('liquid.extension'));
            return $finder;
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerLiquidEngine($resolver)
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->app->singleton('liquid.compiler', function () {
            return new LiquidCompiler(
                $this->app['files'], $this->app['config']['view.compiled']
            );
        });

//        $resolver->register('liquid', function () {
        $this->app->singleton('liquid', function () {
            return new CompilerEngine($this->app['liquid.compiler'], $this->app['config']->get('liquid', []));
        });
    }

    public function registerLiquidExtension()
    {
        $this->app['view']->addExtension(
            $this->app['config']->get('liquid.extension'),
            'liquid',
            function () {
                return $this->app['liquid'];
            }
        );
    }

}