<?php

namespace robrogers3\Laracastle;

use Illuminate\Support\Collection;

class Device
{
    /** @var string */
    protected $token;

    /** @var string */
    protected $ip;

    /** @var Collection */
    protected $location;

    /** @var string */
    protected $city;

    /** @var string */
    protected $region;

    /** @var string */
    protected $country;

    /** @var Collection */
    protected $userAgent;

    /** @var string */
    protected $family;

    /** @var string */
    protected $browser;


    public function __construct($token, array $deviceData)
    {
        $this->token = $token;

        $data = collect(collect($deviceData)->get('context', collect([])));

        $this->ip = $data->get('ip', 'unknown');


        $this->location = collect($data->get('location', []));

        $this->city = $this->location->get('city');

        $this->region = $this->location->get('region');

        $this->country = $this->location->get('country');

        $this->userAgent = collect($data->get('user_agent', []));

        $this->family = $this->userAgent->get('family', 'unknown');

        $this->browser = $this->userAgent->get('browser', 'unknown');

        $this->platform = $this->userAgent->get('platform', 'unknown');
    }

    public function ip()
    {
        return $this->ip;
    }

    public function description()
    {
        return "$this->family on $this->platform";
    }

    public function location()
    {
        if ($this->city) {
            return "$this->city, $this->country";
        }

        return "$this->region, $this->country";
    }
}
