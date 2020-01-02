<?php

namespace App\Providers;

use App\User;
use robrogers3\Laracastle\UserInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        // Laracastle should reference the App\User::class
        // when resolving the UserInterface::class
        $this->app->bind(UserInterface::class, function ($app) {
            return User::class;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
