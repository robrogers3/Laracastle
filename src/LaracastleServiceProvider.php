<?php

namespace robrogers3\Laracastle;

use Illuminate\Support\ServiceProvider;

class LaracastleServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'robrogers3');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {

        require __DIR__ . '/../vendor/autoload.php';
        \Castle::setApiKey(config('laracastle.castle.secret'));
        $this->mergeConfigFrom(__DIR__.'/../config/laracastle.php', 'laracastle');
        $this->app->register(LaracastleEventServiceProvider::class);
        // Register the service the package provides.
        $this->app->bind('Laracastle', function ($app) {
                return new Laracastle;
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laracastle'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laracastle.php' => config_path('laracastle.php'),
        ], 'laracastle.config');

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/robrogers3'),
        ], 'laracastle.views');

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/robrogers3'),
        ], 'laracastle.views');*/
    }
}
