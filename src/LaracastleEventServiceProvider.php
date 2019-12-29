<?php

namespace robrogers3\Laracastle;

use robrogers3\Laracastle\Events\AccountNeedsReview;
use robrogers3\Laracastle\Events\AccountCompromised;
use robrogers3\Laracastle\Listeners\AccountNeedsReviewListener;
use robrogers3\Laracastle\Listeners\AccountCompromisedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Register the events we need to listen to for \Castle::authenticate and \Castle::track
 */
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
        ],
        AccountCompromised::class => [
            AccountCompromisedListener::class
        ],
        AccountNeedsReview::class => [
            AccountNeedsReviewListener::class
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
