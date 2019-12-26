<?php

namespace robrogers3\Laracastle\Listeners;

use robrogers3\Laracastle\Laracastle;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// TO Determine, do we Queue this?
class PasswordResetListener
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
     * @param  PasswordReset  $event
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        $this->laracastle->trackPasswordReset($event);
    }
}
