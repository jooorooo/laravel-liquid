<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 14.2.2019 г.
 * Time: 08:29 ч.
 */

namespace Liquid;

use Illuminate\Support\ServiceProvider;
use Liquid\Template;

class LiquidServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/liquid.php', 'liquid');

        $this->registerCache();
        $this->registerExtension();
        $this->registerEngine();
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/liquid.php' => config_path('liquid.php')
        ], 'config');

    }

    /**
     * Register the Twig extension in the Laravel View component.
     *
     * @return void
     */
    protected function registerExtension()
    {
        $this->app['view']->addExtension(
            $extension = $this->app['config']->get('liquid.extension'),
            'liquid',
            function () {
                return $this->app['liquid'];
            }
        );

        $finder = $this->app['view.finder'];
        $finder->addExtension($extension);
        $this->app['view.finder'] = $finder;
    }
    /**
     * Register Twig engine bindings.
     *
     * @return void
     */
    protected function registerEngine()
    {
        $this->app->bindIf('liquid', function () {
                return new LiquidEngine($this->app['view.finder'], $this->app['liquid.cache'], $this->app['config']->get('liquid.cache.expire'));
            }, true);

    }
    /**
     * Register Twig engine bindings.
     *
     * @return void
     */
    protected function registerCache()
    {
        $this->app->bindIf('liquid.cache', function () {
                return $this->app['cache']->driver($this->app['config']->get('liquid.cache.driver'));
            }, true);

    }

}