<?php

namespace robrogers3\Laracastle\Listeners;

use robrogers3\laracastle\Laracastle;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoginListener
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
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $this->laracastle->authenticate($event);
    }
}
