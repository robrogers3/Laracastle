<?php

namespace robrogers3\Laracastle;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use robrogers3\Laracastle\Http\Controllers\WebHookController;



class LaracastleServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'robrogers3');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        Route::group(['middleware' => 'web'], function () {
            Route::get('laracastle/review-device/{user_id}/{device_token}',
                       '\robrogers3\Laracastle\Http\Controllers\DevicesController@show')
                ->middleware('auth')
                ->name('laracastle.review-device');

            Route::post('laracaste/review-device/',
                        '\robrogers3\Laracastle\Http\Controllers\DevicesController@store')
                ->middleware('auth')
                ->name('laracastle.report-device');

            Route::delete('laracaste/review-device/',
                          '\robrogers3\Laracastle\Http\Controllers\DevicesController@destroy')
                ->middleware('auth')
                ->name('laracastle.approve-device');


            Route::post('laracastle/compromised-webhook',
                        '\robrogers3\Laracastle\Http\Controllers\WebHookController@compromised')
                ->name('laracastle.compromised-webhook');
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/../config/laracastle.php', 'laracastle');

        \Castle::setApiKey(config('laracastle.castle.secret'));

        $this->app->register(LaracastleEventServiceProvider::class);

        // Register the service the Laracastle provides.
        $this->app->singleton('Laracastle', function ($app) {
            return new \robrogers3\Laracastle\Laracastle;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Laracastle'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        Auth::routes();
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
