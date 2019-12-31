<?php

namespace robrogers3\Laracastle\Repositories;

use robrogers3\Laracastle\Device;

interface DeviceRepositoryInterface
{
    public function setToken($token);

    public function getDevice($with = null);

    public function fetchData($with = null);

}
