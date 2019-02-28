<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 14.2.2019 г.
 * Time: 08:29 ч.
 */

namespace Liquid;

use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider;

class LiquidServiceProvider extends ViewServiceProvider
{

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__ . '/../../config/liquid.php', 'liquid');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/liquid.php' => config_path('liquid.php')
        ], 'config');

    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->app->singleton('view.engine.resolver', function () {
            $resolver = new EngineResolver();

            // Next, we will register the various view engines with the resolver so that the
            // environment will resolve the engines needed for various views based on the
            // extension of view file. We call a method for each of the view's engines.
            foreach (['file', 'php', 'blade', 'liquid'] as $engine) {
                $this->{'register'.ucfirst($engine).'Engine'}($resolver);
            }

            return $resolver;
        });
    }

    /**
     * Create a new Factory Instance.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return \Illuminate\View\Factory
     */
    protected function createFactory($resolver, $finder, $events)
    {
        $factory = new Factory($resolver, $finder, $events);
        $factory->addExtension($this->app['config']->get('liquid.extension'), 'liquid');
        return $factory;
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerLiquidEngine($resolver)
    {
        $resolver->register('liquid', function () {
            //Merge liqud config
            Liquid::$config = array_merge(Liquid::$config, config('liquid.liquid', []));

            return new LiquidEngine($this->app['view.finder'], $this->app['files'], $this->app['config']['view.compiled']);
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            $finder = new FileViewFinder($app['files'], $app['config']['view.paths']);
            $finder->addExtension($this->app['config']->get('liquid.extension'));
            return $finder;
        });
    }

}