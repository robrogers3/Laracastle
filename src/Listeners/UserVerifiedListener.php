<?php

namespace robrogers3\Laracastle\Listeners;

use robrogers3\Laracastle\Laracastle;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// TO Determine, do we Queue this? Currently it does
class UserVerifiedListener implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(Verified $event)
    {
        $this->laracastle->trackVerified($event);
    }
}
