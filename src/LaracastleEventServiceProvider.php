<?php

namespace robrogers3\Laracastle;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;


class LaracastleEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            Listeners\LoginListener::class
        ],
        Failed::class => [
            Listeners\FailedLoginListener::class,
        ],
        Logout::class => [
            Listeners\LogoutListener::class,
        ],
        PasswordReset::class => [
            Listeners\PasswordResetListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
