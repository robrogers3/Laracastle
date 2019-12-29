<?php

namespace robrogers3\Laracastle\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class AccountNeedsReview
{
    use Dispatchable, SerializesModels;

    public $user;

    public $token;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }
}
