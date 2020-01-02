<?php

namespace robrogers3\Laracastle\Traits;

use robrogers3\Laracastle\Notifications\AccountReset;
use Illuminate\Support\Str;

trait ResetsAccount
{
    public function resetAccountPassword()
    {
        $this->password = bcrypt(Str::random(35));
        $this->save();
        $token = app('auth.password.broker')->createToken($this);
        $this->notify(new AccountReset($token));
    }

    public function resetsEmailVerification()
    {
        $this->email_verified_at = null;
        $this->save();
        $this->sendEmailVerificationNotification();
    }
}
