<?php

namespace robrogers3\Laracastle\Repositories;

use Zttp\Zttp;
use robrogers3\Laracastle\Device;

class DeviceRepository
{

    /** @var string */
    protected $token;

    /** @var Device */
    protected $device;


    public function __construct($token)
    {
        $this->token = $token;
    }

    public function getDevice($with = null)
    {
        return new Device($this->token, $this->fetchData($with));
    }

    public function fetchData($with)
    {
        if ($with) {
            return json_decode($with, true);
        }

        $url = config('laracastle.castle.devices_path') . $this->token;

        $encoded_secret = base64_encode(':' . config('laracastle.castle.secret'));

        $response = Zttp::withHeaders([
            "Authorization" => "Basic $encoded_secret"
        ])->get($url);

        if (!$response->isOk()) {
            abort(404);
        }

        $data = $response->json();

        return $data;
    }
}
