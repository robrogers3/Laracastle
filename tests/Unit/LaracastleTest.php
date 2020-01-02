<?php

namespace robrogers3\Laracastle\Tests\Unit;

use Carbon\Carbon;
use Mockery;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use robrogers3\Laracastle\UserInterface;
use robrogers3\Laracastle\Laracastle;
use robrogers3\Laracastle\Traits\ChecksVerification;
use robrogers3\Laracastle\Traits\ResetsAccount;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Orchestra\Testbench\TestCase;


class LaracastleTest extends TestCase
{
    public $user;
    public $event;

    public function setUp() :void
    {
        parent::setUp();
        Config::set('laracastle.castle.mode', 'running');
        $this->user = $user = new User(1,'robrogers@example.com', '2020-01-01', Carbon::now()->subDays(1));
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
    public function it_does_nothing_in_evaluation_mode()
    {
        Config::set('laracastle.castle.mode', 'evaluation');
        $verdict = new Verdict('deny');
        $castler = new TesterCastler($verdict);
        $laracastle = new Laracastle($castler);
        Auth::shouldReceive('logout')->never();
        $laracastle->authenticate($this->event);
    }

    /**
     * @test
     */
    public function it_kills_the_login_when_the_deny_verdict_is_made()
    {
        $verdict = new Verdict('deny');
        $castler = new TesterCastler($verdict);
        $laracastle = new Laracastle($castler);
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
        $castler = new TesterCastler($verdict);
        $laracastle = new Laracastle($castler);
        $user = new User(1,'robrogers@example.com', '2020-01-01', Carbon::now()->subDays(1));
        $event = new TesterEvent($user, 'web');
        Auth::shouldReceive('logout')->never();
        $laracastle->authenticate($event);
        $this->assertNull($user->email_verified_at);

    }
    /** @test */
    public function it_does_nothing_on_challenge_if_the_user_has_recently_verified_email()
    {
        $user = new User(1,'robrogers@example.com', '2020-01-01', Carbon::now()->subMinutes(1));
        $event = new TesterEvent($user, 'web');
        $verdict = new Verdict('challenge');
        $castler = new TesterCastler($verdict);
        $laracastle = new Laracastle($castler);
        Auth::shouldReceive('logout')->never();
        $laracastle->authenticate($event);
        $this->assertNotNull($user->email_verified_at);

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

    /** @test */
    public function it_does_nothing_on_report()
    {
        $castler = new TesterCastler(null);
        $laracastle = new Laracastle($castler);
        $tracked = $laracastle->report('some-token');
        $this->assertSame([
            "event" => "\$review.escalated",
            "device_token" => "some-token"
        ], $tracked);
    }

    /** @test */
    public function it_does_nothing_on_approve()
    {
        $castler = new TesterCastler(null);
        $laracastle = new Laracastle($castler);
        $tracked = $laracastle->approve('some-token');
        $this->assertSame([
            "event" => '$challenge.succeeded',
            "device_token" => 'some-token'
        ], $tracked);
    }
}

/**
 * fakes for testing
 */
class User implements MustVerifyEmail, UserInterface
{
    use ChecksVerification, ResetsAccount;

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
    public function sendEmailVerificationNotification() {
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification() {}

    public function getKey()
    {
        return 'id';
    }
}
class Verdict
{
    public $action;
    public function  __construct($action)
    {
        $this->action = $action;
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
