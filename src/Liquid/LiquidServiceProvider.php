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

        $this->mergeConfigFrom($file = __DIR__ . '/../../config/liquid.php', 'liquid');

        $this->publishes([
            $file => config_path('liquid.php')
        ], 'config');
    }

    public function boot()
    {
        $this->app['view']->addExtension($this->app['config']->get('liquid.extension'), 'liquid', function() {
            $engine =  new LiquidEngine($this->app['view.finder'], $this->app['files'], $this->app['config']['view.compiled']);
            if($tags = $this->app['config']->get('liquid.tags', [])) {
                foreach($tags AS $tag => $object) {
                    $engine->registerTag($tag, $object);
                }
            }
            return $engine;
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