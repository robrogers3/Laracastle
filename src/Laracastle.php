<?php

namespace robrogers3\Laracastle;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\MustVerifyEmail;

/**
 * Laracastle is the integration point betwenn Laravel and Castle.io
 *
 */
class Laracastle
{
    /** @var \Castle|TestCastle */
    public $castler;

    /**
     * allow us to override castle class, good for testing since we cannot easily mock static function calls
     * @param $castler
     */
    public function __construct($castler = null)
    {
        if ($castler) {
            $this->castler = $castler;
        } else {
            $this->castler = \Castle::class;
        }

    }

    /**
     * @param  Login  $event
     * @return void
     */
    public function authenticate($event)
    {
        $verdict = null;

        try {
            //$_SERVER['REMOTE_ADDR'] = '5.2.77.146';
            //$_SERVER['HTTP_USER_AGENT'] = 'dude';
            //$context = \Castle_RequestContext::extractJson();
            $verdict = $this->castler::authenticate([
                'event' => '$login.succeeded',
                'user_id' => $event->user->id,
                'user_traits' => [
                    'email' => $event->user->email ?: 'not set',
                    'registered_at' => $event->user->created_at ?: 'not set'
                ]
            ]);

        } catch (\Exception $e) {
            Log::info(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
            return;
        }

        if (!$verdict) {
            //should not get here! We shoud have a $verdict
            //catch should catch it!
            Log::warning(__METHOD__, ['message' => 'veridict not returned from castle']);
            return;
        }

        //if in evaluation mode, don't do anything
        //@see laracastle config
        if (config('laracastle.castle.mode') == 'evaluation') {
            //just log verdict
            Log::warning(__METHOD__, ['verdict' => $verdict->action]);
            return;
        }

        if ($verdict->action == 'allow') {
            //we have already logged in so we are good to go
            //TODO: remove this line after dev
            Log::debug(__METHOD__, ['verdict' => $verdict->action]);
            return;

        }

        if ($verdict->action == 'challenge') {
            if (! $event->user instanceof MustVerifyEmail) {
                //TODO Perhaps do event which sends an email instead notifying user?
                //TODO add recapcha to the page
                //TODO figure out if we should do anything
                Log::warning(__METHOD__, ['needs challenge' => 'but we dont have one yet', 'user' => $event->user]);
                return;
            }

            // instead of showing a challenge make them ReVerifyEmail
            // should be configurable
            if ($event->user->recentlyVerified()) {
                Log::debug(__METHOD__, ['message' => 'they just verified we should send Castle a notice on verification.', 'user' => $event->user]);
                return;
            }

            $event->user->resetsEmailVerification();
            //we make them verify their email as challenge
            return;
        }

        if ($verdict->action == 'deny') {
            //Laravel will pick this up after 5 attempts in throttling period
            //and lock user out for period.
            //configurable.
            Auth::logout();
        }
    }

    /**
     * @param  Failed  $event
     * @return void
     */
    public function trackFailed($event)
    {
        try {
            return $this->castler::track([
                'event' => '$login.failed',
                'user_id' => $event->user->id ?? null,
                'user_traits' => [
                    'email' => $event->user->email ?? 'not found',
                    'registered_at' => $event->user->created_at ?? 'not found'
                ]
            ]);
        } catch (\Exception $e) {
            Log::warning(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Logout  $event
     * @return void
     */
    public function trackLogout($event)
    {
        try {
            return $this->castler::track([
                'event' => '$logout.succeeded',
                'user_id' => $event->user->id
            ]);
        } catch (\Exception $e) {
            Log::warning(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param  PasswordReset  $event
     * @return void
     */
    public function trackPasswordReset($event)
    {
        try {
            return $this->castler::track([
                'event' => '$password_reset.succeeded',
                'user_id' => $event->user->id
            ]);
        } catch (\Exception $e) {
            Log::warning(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param string $token
     * @retuct array
     */
    public function approve($token)
    {
        try {
            return $this->castler::track([
                'event' => '$challenge.succeeded',
                'device_token' => $token
            ]);
        } catch (\Exception $e) {
            Log::warning(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param string $token
     * @return array
     */
    public function report($token)
    {
        try {
            return $this->castler::track([
                'event' => '$review.escalated',
                'device_token' => $token
            ]);
        } catch (\Exception $e) {
            Log::warning(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
        }
    }

}
