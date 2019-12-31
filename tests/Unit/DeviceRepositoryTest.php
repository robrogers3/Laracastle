<?php

namespace robrogers3\Laracastle\Tests\Unit;

use Orchestra\Testbench\TestCase;
use robrogers3\Laracastle\Device;
use robrogers3\Laracastle\Repositories\DeviceRepository;

class DeviceRepositoryTest extends TestCase
{
    /** @test */
    public function it_makes_a_device_from_the_repos()
    {
        $token = "19a0g9Hn84vkNSvRG6F9qM4j";
        $repo = (new DeviceRepository())->setToken($token);
        $device = $repo->getDevice($this->device_data());
        $this->assertInstanceOf(Device::class, $device);
        $this->assertSame('162.12.41.13', $device->ip());
    }

    protected function device_data()
    {
        return '{
		"token": "19a0g9Hn84vkNSvRG6F9qM4j",
		"object": "device",
		"created_at": "2017-12-28T17:22:40.556Z",
		"last_seen_at": "2018-06-11T17:10:26.928Z",
		"approved_at": null,
		"escalated_at": "2018-06-12T17:10:26.928Z",
		"mitigated_at": null,
		"risk": 1.0,
		"context": {
			"type": "desktop",
			"ip": "162.12.41.13",
			"user_agent": {
				"raw": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko)",
				"browser": "Chrome",
				"version": "42.0.2311",
				"os": "Mac OS X 10.9.5",
				"mobile": false,
				"platform": "Mac OS X",
				"device": "Unknown",
				"family": "Chrome"
			},
			"location": {
				"street": null,
				"city": "San Francisco",
				"postal_code": 94107,
				"region": "CA",
				"country": "US",
				"lon": -122.3870544,
				"lat": 37.8019832
			}
		}
	   }';
    }
}
