<?php

namespace robrogers3\Laracastle\Facades;

use Illuminate\Support\Facades\Facade;

class Laracastle extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Laracastle';
    }
}
