<?php

namespace robrogers3\Laracastle\Repositories;

use robrogers3\Laracastle\User;

class UserRepository
{
    public function findById($id)
    {

        $user =  User::findOrFail($id);
        return $user;
    }
}
