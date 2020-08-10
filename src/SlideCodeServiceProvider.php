<?php

namespace Tncode;

class SlideCodeServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        // $this->loadViewsFrom(__DIR__ . '/slide-code.blade.php', 'slide-code');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(SlideCode::class, function () {
            return new SlideCode();
        });

        $this->app->alias(SlideCode::class, 'slide_code');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [SlideCode::class, 'slide_code'];
    }
}