<?php

namespace robrogers3\Laracastle\Repositories;

use robrogers3\Laracastle\UserInterface;

class UserRepository
{
    public function findById($id)
    {
        return resolve(UserInterface::class)::findOrFail($id);
    }
}
