<?php

namespace robrogers3\Laracastle\Listeners;

use robrogers3\Laracastle\Events\AccountCompromised;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AccountCompromisedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AccountCompromised  $event
     * @return void
     */
    public function handle(AccountCompromised $event)
    {
        /** App\User; */
        $user = $event->user;
        $user->resetAccountPassword();
    }
}
