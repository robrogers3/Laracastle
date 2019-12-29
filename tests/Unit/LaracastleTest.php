<?php

namespace robrogers3\Laracastle\Tests\Unit;

use Mockery;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use robrogers3\Laracastle\Laracastle;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Orchestra\Testbench\TestCase;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LaracastleTest extends TestCase
{
    public $user;
    public $event;

    public function setUp() :void
    {
        parent::setUp();
        Config::set('laracastle.castle.mode', 'running');
        $this->user = $user = new User(1,'robrogers@example.com', '2020-01-01', '2020-01-01');
        $this->event = $event = new TesterEvent($user, 'web');
    }

    /** required for testing package */
    protected function getPackageProviders($app)
    {
        return ['robrogers3\Laracastle\LaracastleServiceProvider'];
    }

    /**
     * @test
     */
    public function it_does_noting_in_evaluation_mode()
    {
        Config::set('laracastle.castle.mode', 'evaluation');
        $verdict = new Verdict('deny');
        $laracastle = new TesterLaracastle($verdict);
        Auth::shouldReceive('logout')->never();
        $laracastle->authenticate($this->event);
    }

    /**
     * @test
     */
    public function it_kills_the_login_when_the_deny_verdict_is_made()
    {
        $verdict = new Verdict('deny');
        $laracastle = new TesterLaracastle($verdict);
        Auth::shouldReceive('logout')->once();
        $laracastle->authenticate($this->event);
    }

    /**
     * @test
     * Note: this assumes users implmenents MustVerifyEmail
     */
    public function it_nulls_out_email_verified_at_on_challenge()
    {
        $verdict = new Verdict('challenge');
        $laracastle = new TesterLaracastle($verdict);
        Auth::shouldReceive('logout')->never();
        $laracastle->authenticate($this->event);
        $this->assertNull($this->user->email_verified_at);
    }

    /** @test */
    public function it_calls_track_on_logout_event()
    {

        $castler = new TesterCastler(null);
        $laracastle = new Laracastle($castler);
        $tracked = $laracastle->trackLogout($this->event);
        $this->assertSame($tracked, [
            'event' => '$logout.succeeded',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_calls_track_on_login_failed()
    {

        $castler = new TesterCastler(null);
        $laracastle = new Laracastle($castler);
        $tracked = $laracastle->trackFailed($this->event);
        $this->assertSame($tracked, [
            'event' => '$login.failed',
            'user_id' => $this->user->id,
            'user_traits' => [
                'email' => $this->user->email,
                'registered_at' => $this->user->created_at
            ]
        ]);
    }

    /** @test */
    public function it_calls_track_on_password_reset()
    {
        $castler = new TesterCastler(null);
        $laracastle = new Laracastle($castler);
        $tracked = $laracastle->trackPasswordReset($this->event);
        $this->assertSame($tracked, [
            'event' => '$password_reset.succeeded',
            'user_id' => $this->user->id
        ]);
    }
}

/**
 * fixtures?
 * fakes for testing
 */
class User implements MustVerifyEmail
{
    public $id;
    public $email;
    public $created_at;
    public $email_verified_at;

    public function __construct($id, $email, $created_at, $email_verified_at)
    {
        $this->id = $id;
        $this->email = $email;
        $this->created_at = $created_at;
        $this->email_verified_at = $email_verified_at;
    }
    public function save()
    {
        $this->email_verified_at = null;
    }
    public function hasVerifiedEmail() {}

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified() {}

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification() {}

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification() {}
}
class Verdict
{
    public $action;
    public function  __construct($action)
    {
        $this->action = $action;
    }
}
class TesterLaracastle extends Laracastle
{
    public $castler;
    public function __construct($verdict)
    {
        $this->castler = new TesterCastler($verdict);

    }
}
class TesterCastler
{
    public static $verdict;
    public function __construct($verdict)
    {
        self::$verdict = $verdict;
    }
    public static function authenticate(array $args)
    {
        return self::$verdict;
    }

    public static function track(array $args)
    {
        return $args;
    }
}
class TesterEvent
{
    public $user;
    public $gaurd;

    public function __construct($user, $guard)
    {
        $this->user = $user;
        $this->guard = $guard;
    }
}
