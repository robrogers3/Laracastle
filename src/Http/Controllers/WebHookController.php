<?php

namespace robrogers3\Laracastle\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use robrogers3\Laracastle\Events\AccountCompromised;
use robrogers3\Laracastle\Repositories\UserRepository;

class WebHookController extends Controller
{
    public function compromised(Request $request)
    {
        $hookRequest = json_decode($request->getContent(), true);

        if (!isset($hookRequest['type'])
            || $hookRequest['type'] !== '$incident.confirmed') {
            return;
        }

        if (!isset($hookRequest['data']) || !isset($hookRequest['data']['user_id'])) {
            return;
        }

        try {
            $user = (new UserRepository())->findById($hookRequest['data']['user_id']);
            event(new AccountCompromised($user));
        } catch (ModelNotFoundException $e) {
            //Cant do anything about it!
            Log::info(__METHOD__, ['exception' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
            throw $e;
        }
        return $hookRequest['type'];
    }

    public function review(Request $request)
    {
        $hookRequest = json_decode($request->getContent(), true);

        if (!isset($hookRequest['type'])
            || $hookRequest['type'] !== '$review.opened') {
            return $hookRequest['type'];
        }

        if (!isset($hookRequest['data']) || !isset($hookRequest['data']['user_id'])) {
            return;
        }

        try {
            $user = (new UserRepository())->findById($hookRequest['data']['user_id']);
            event(new AccountNeedsReview($user));
        } catch (ModelNotFoundException $e) {
            //Cant do anything about it!
            Log::warning(__METHOD__, ['exception' => $e->getMessage()]);
        } catch (\Exception $e) {
            //let's find out what other problems we can get!
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
            throw $e;
        }

        return $hookRequest['type'];
    }
p}
