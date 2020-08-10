<?php

namespace Tncode;

class SlideCaptchaServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {

    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(SlideCaptcha::class, function () {
            return new SlideCaptcha();
        });

        $this->app->alias(SlideCaptcha::class, 'slide_captcha');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [SlideCaptcha::class, 'slide_captcha'];
    }
}