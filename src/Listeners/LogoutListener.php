<?php

namespace robrogers3\Laracastle\Listeners;

use robrogers3\Laracastle\Laracastle;
use Illuminate\Auth\Events\Logout;


class LogoutListener
{
    protected $laracastle;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Laracastle $laracastle)
    {
        $this->laracastle = $laracastle;
    }

    /**
     * Handle the event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $this->laracastle->trackLogout($event);
    }
}
