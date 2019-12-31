<?php

namespace robrogers3\Laracastle\Listeners;

use robrogers3\Laracastle\Notifications\AccountReview;
use robrogers3\Laracastle\Events\AccountNeedsReview;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AccountNeedsReviewListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  AccountCompromised  $event
     * @return void
     */
    public function handle(AccountNeedsReview $event)
    {
        /** App\User; */
        $user = $event->user;
        $token = $event->token;
        $user->notify(new AccountReview($token));
    }
}
