<?php

namespace robrogers3\Laracastle\Tests\Feature;

use Mockery;
use robrogers3\Laracastle\Tests\User;
use robrogers3\Laracastle\Laracastle;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use robrogers3\Laracastle\Events\AccountNeedsReview;
use robrogers3\Laracastle\Events\AccountCompromised;
use robrogers3\Laracastle\Notifications\AccountReset;
use robrogers3\Laracastle\Notifications\AccountReview;

use Orchestra\Testbench\TestCase;

class LaracastleEventsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
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

    //TODO delete these non-test methods
    /** test */
    public function we_are_listening_for_login_events()
    {
        Event::fake();
        $user = factory(User::class)->create([
            'email' => $email = 'robertbrogers@gmail.com',
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);
        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
        $_SERVER['REMOTE_ADDR'] = '192.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'dude';

        Event::assertDispatched(Login::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    public function castle_is_called_on_login()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);

        $spy = $this->spy(Laracastle::class);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $spy->shouldHaveReceived('authenticate')->once();
    }

    public function castle_is_called_on_failure()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);

        $spy = $this->spy(Laracastle::class);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-one',
        ]);

        //$response->assertRedirect('/home');
        //$this->assertAuthenticatedAs($user);
        $spy->shouldHaveReceived('trackFailed')->once();
    }

    /** @test */
    public function it_calls_authenticate_on_login()
    {
        $spy = $this->spy(Laracastle::class);
        $user = Mockery::mock(User::class)->makePartial();
        Event::dispatch(new Login('web', $user, false));
        $spy->shouldHaveReceived('authenticate')->once();

    }
    /** @test */
    public function it_calls_track_on_login_failure()
    {
        $spy = $this->spy(Laracastle::class);
        $user = Mockery::mock(User::class)->makePartial();
        Event::dispatch(new Failed('web', $user, false));
        $spy->shouldHaveReceived('trackFailed')->once();
    }

    /** @test */
    public function it_calls_track_on_logout()
    {
        $spy = $this->spy(Laracastle::class);
        $user = Mockery::mock(User::class)->makePartial();
        Event::dispatch(new Logout('web', $user, false));
        $spy->shouldHaveReceived('trackLogout')->once();
    }

    /** @test */
    public function it_calls_track_on_password_reset()
    {
        $spy = $this->spy(Laracastle::class);
        $user = Mockery::mock(User::class)->makePartial();
        Event::dispatch(new PasswordReset('web', $user, false));
        $spy->shouldHaveReceived('trackPasswordReset')->once();
    }

    /** @test */
    public function it_triggers_reset_accounts_on_account_compromised()
    {
        Notification::fake();
        Event::dispatch(new AccountCompromised($this->user));
        Notification::assertSentTo(
            [$this->user], AccountReset::class
        );

    }

    /** @test */
    public function it_triggers_a_review_device_notification_when_device_needs_review()
    {
        Notification::fake();
        $user = Mockery::mock(User::class)->makePartial();
        Event::dispatch(new AccountNeedsReview($user, $token="ABCD"));
        Notification::assertSentTo(
            [$user], AccountReview::class,
            function($notification, $channels) use ($token) {
                return $notification->token == $token;
            }
        );
    }

    /** @test */
    public function it_tracks_when_user_has_verified_email()
    {
        $spy = $this->spy(Laracastle::class);
        Event::dispatch(new Verified('user'));
        $spy->shouldHaveReceived('trackVerified')->once();
    }
}
