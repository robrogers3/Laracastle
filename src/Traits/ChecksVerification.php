<?php

namespace robrogers3\Laracastle\Traits;

use Carbon\Carbon;

trait ChecksVerification
{
    public function recentlyVerified()
    {
        if (is_null($this->email_verified_at)) {
            return false;
        }

        return $this->email_verified_at->gt(Carbon::now()->subMinutes(config('laracastle.options.verified_ok_minutes')));
    }

}
