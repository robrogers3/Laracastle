<?php

namespace robrogers3\Laracastle\Listeners;

use robrogers3\laracastle\Laracastle;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// TO Determine, do we Queue this?
class FailedLoginListener
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
     * @param  Failed  $event
     * @return void
     */
    public function handle(Failed $event)
    {
        $this->laracastle->trackFailed($event);
    }
}
