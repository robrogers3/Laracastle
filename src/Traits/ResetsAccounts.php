<?php

namespace robrogers3\Laracastle\Traits;

use robrogers3\Laracastle\Notifications\AccountReset;
use Illuminate\Support\Str;

trait ResetsAccounts
{
    public function resetAccountPassword()
    {
        $this->password = bcrypt(Str::random(35));
        $this->save();
        $token = app('auth.password.broker')->createToken($this);
        $this->notify(new AccountReset($token));
    }

}

