<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;
use robrogers3\Laracastle\Repositories\DeviceRepository;
use robrogers3\Laracastle\Repositories\DeviceRepositoryInterface;
use robrogers3\Laracastle\Tests\Device;
use robrogers3\Laracastle\Tests\User;
use robrogers3\Laracastle\Events\AccountCompromised;
use robrogers3\Laracastle\Events\AccountNeedsReview;

class RoutesTest extends TestCase
{
	protected $user;

	protected $token;

	/**
	 * Setup the test environment.
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
		$this->token = "eyJhbGciOiJIUzI1NiJ9.eyJ0b2tlbiI6ImpGM1RXMldGRUhqOGtaaWdjZ0E3ak1wczNydHM5ZVg1IiwidmVyc2lvbiI6MC4xfQ.u-9pAtDph_KoOvJqo7DaOm5izQ_ZXlrmiyFKRt7Wjrg";
		$this->user = new User();
		$this->user->name = 'Rob';
		$this->user->email = 'robrogers@me.com';
		$this->user->password = $passward = bcrypt('password');
		$this->user->save();
	}

	protected function getPackageProviders($app)
	{
		return ['robrogers3\Laracastle\LaracastleServiceProvider'];
	}

	protected function getEnvironmentSetup($app)
	{
		Route::get('/home', 'HomeController@index')
			->middleware('verified')
			->name('home');
		return;
		if (!class_exists(\CreateUsersTable::class)) {
			include_once __DIR__ . '/../../database/migrations/2014_10_12_000000_create_users_table.php';
			(new \CreateUsersTable)->up();
		} else {
		}
	}


	/** @test */
	public function it_responds_to_the_compromised_webhooks_route()
	{
		$this->actingAs($this->user);
		$this->withoutExceptionHandling();
		$hookData = json_decode($this->getIncidentConfirmedWebhookJson(), true);
		Event::fake();
		$this->json('POST', route('laracastle.compromised-webhook'), $hookData)
			->assertOk()
			->assertSee('$incident.confirmed');
		Event::assertDispatched(AccountCompromised::class, function ($e) {
			return $e->user->id == $this->user->id;
		});
	}


	/** @test */
	public function it_responds_to_the_review_webhooks_route()
	{
		$this->actingAs($this->user);
		$this->withoutExceptionHandling();
		Event::fake();
		$hookData = json_decode($this->getReviewWebhookJson(), true);
		$this->json('POST', route('laracastle.review-webhook'), $hookData)
			->assertOk()
			->assertSee('$review.opened');

		Event::assertDispatched(AccountNeedsReview::class, function ($e) {
			return $e->user->id == $this->user->id;
		});
	}

	/** @test */
	public function it_shows_a_users_device_given_a_device_token()
	{
		$this->withoutExceptionHandling();

		$this->actingAs($this->user)
			->get(route('laracastle.review-device', [1, $this->token]))
			->assertOk()
			->assertSee($this->token)
			->assertSee('Safari');
	}

	/** @test */
	public function it_reports_a_device()
	{
		$spy = $this->spy(Laracastle::class);
		$this->withoutExceptionHandling();
		$this->actingAs($this->user)
			->post(route('laracastle.report-device'), ['token' => $this->token])
			->assertRedirect('/home');
		$spy->shouldHaveReceived('report')->once();
	}

	/** @test */
	public function it_approves_a_device()
	{
		$spy = $this->spy(Laracastle::class);
		$this->withoutExceptionHandling();
		$this->actingAs($this->user)
			->delete(route('laracastle.approve-device'), ['token' => $this->token])
			->assertRedirect('/home');
		$spy->shouldHaveReceived('approve')->once();
	}

	protected function getDeviceData()
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
				"city": "San Francsico",
				"postal_code": 94107,
				"region": "CA",
				"country": "US",
				"lon": -122.3870544,
				"lat": 37.8019832
			}
		}
	}';
	}
	protected function getIncidentConfirmedWebhookJson()
	{
		return '{
		"api_version": "v1",
		"app_id": "382395555537961",
		"type": "$incident.confirmed",
		"created_at": "2019-12-01T19:38:28.483Z",
		"data": {
			"id": "test",
			"device_token": "eyJhbGciOiJI1NiJ9.eyJ0b2tlbiI6InRlc3QiLCJzaW9uIjowLjF9._-0l6TlDH7m78l19z1amMQ02m7s",
			"user_id": "1",
			"trigger": "$login.succeeded",
			"context": {
				"ip": "172.56.39.210",
				"isp": {
					"isp_name": "CastleNet",
					"isp_organization": "Castle"
				},
				"location": {
					"country_code": "US",
					"country": "United States",
					"region": "California",
					"region_code": "CA",
					"city": "San Francisco",
					"lat": 37.8019832,
					"lon": -122.3870544
				},
				"user_agent": {
					"raw": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko)",
					"browser": "Chrome",
					"version": "42.0.2311",
					"os": "Mac OS X 10.9.5",
					"mobile": false,
					"platform": "Mac OS X",
					"device": "Unknown",
					"family": "Chrome"
				}
			}
		}
	}';
	}

	protected function getReviewWebhookJson()
	{
		return '{
		"api_version": "v1",
		"app_id": "382395555537961",
		"type": "$review.opened",
		"created_at": "2018-06-01T19:38:28.483Z",
		"data": {
			"id": "test",
			"device_token": "eyJhbGciOiJI1NiJ9.eyJ0b2tlbiI6InRlc3QiLCJzaW9uIjowLjF9._-0l6TlDH7m78l19z1amMQ02m7s",
			"user_id": "1",
			"trigger": "$login.succeeded",
			"context": {
				"ip": "172.56.39.210",
				"isp": {
					"isp_name": "CastleNet",
					"isp_organization": "Castle"
				},
				"location": {
					"country_code": "US",
					"country": "United States",
					"region": "California",
					"region_code": "CA",
					"city": "San Francisco",
					"lat": 37.8019832,
					"lon": -122.3870544
				},
				"user_agent": {
					"raw": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko)",
					"browser": "Chrome",
					"version": "42.0.2311",
					"os": "Mac OS X 10.9.5",
					"mobile": false,
					"platform": "Mac OS X",
					"device": "Unknown",
					"family": "Chrome"
				}
			}
		}
	 }';
	}
}
