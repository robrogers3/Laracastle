<?php

namespace robrogers3\Laracastle\Tests;

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

    }

    public function ip()
    {
        return '192.168.1.1';
    }

    public function description()
    {
        return "Chrome on Mac OS X";
    }

    public function location()
    {
        return "Richmond, VA";
    }

    public function token()
    {
        return $this->token;
    }
}
