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

        $this->app['view']->addExtension($this->app['config']->get('liquid.extension'), 'liquid', function() {
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