<?php

namespace robrogers3\Laracastle;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Laracastle
{
    public $castler;

    public function __construct($castler = null)
    {
        if ($castler == null) {
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
            Log::warn(__METHOD__, ['message' => 'veridict not returned from castle']);
            return;
        }


        //if in evaluation mode, don't do anything
        //@see laracastle config
        if (config('laracastle.castle.mode') == 'evaluation') {
            //just log verdict
            Log::debug(__METHOD__, ['verdict' => $verdict->action]);
            return;
        }

        if ($verdict->action == 'allow') {
            //we have already logged in so we are good to go
            //TODO: remove this line after dev
            Log::debug(__METHOD__, ['verdict' => $verdict->action]);
        } else if ($verdict->action == 'challenge') {
            // instead of showing a challenge make them ReVerifyEmail
            // should be configurable
            if ($event->user instanceof MustVerifyEmail && $event->user->email_verified_at) {
                $event->user->email_verified_at = null;
                $event->user->save();
            } else {
                //TODO Perhaps do event which sends an email instead notifying user?
                //TODO add recapcha to the page
                Auth::logout();
                throw ValidationException::withMessages([
                    'password' => [trans('auth.failed')],
                ]);
            }
        } else if ($verdict->action == 'deny') {
            //Laravel will pick this up after 5 attempts in throttling period and lock user out for period. 
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
            \Castle::track([
                'event' => '$login.failed',
                'user_id' => $event->user->id ?: -1,
                'user_traits' => [
                    'email' => $event->user->email ?: 'not found',
                    'registered_at' => $event->user->created_at ?: 'not found'
                ]
            ]);
        } catch (\Exception $e) {
            Log::debug(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Logout  $event
     * @return void
     */
    public function trackLogout($event)
    {
        try {
            \Castle::track([
                'event' => '$logout.succeeded',
                'user_id' => $event->user->id
            ]);
        } catch (\Exception $e) {
            Log::debug(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
        }
        Log::info(__METHOD__, ['logout' => 'does this need tracking']);
    }

    /**
     * @param  PasswordReset  $event
     * @return void
     */
    public function trackPasswordReset($event)
    {
        Log::debug(__METHOD__, ['event' => $event]);
        try {
            \Castle::track([
                'event' => '$password_reset.succeeded',
                'user_id' => $event->user->id
            ]);
        } catch (\Exception $e) {
            Log::debug(__METHOD__, ['event' => $e, 'error' => $e->getMessage()]);
        }
    }
}
