<?php

namespace robrogers3\Laracastle\Repositories;

use Illuminate\Foundation\Auth\User;

class UserRepository
{
    public function findById($id)
    {
        return User::findOrFail($id);
    }
}
