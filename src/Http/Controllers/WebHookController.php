<?php

namespace robrogers3\Laracastle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use robrogers3\Laracastle\Events\AccountCompromised;
use robrogers3\Laracastle\Events\AccountNeedsReview;
use robrogers3\Laracastle\Repositories\UserRepository;

class WebHookController extends Controller
{
    /**
     * @param Request $request
     * @return string
     */
    public function compromised(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            throw ValidationException::withMessages([
                'request' => ['castle signature not valid']
            ]);
        }

        $hookRequest = json_decode($request->getContent(), true);

        try {
            if (!isset($hookRequest['type'])
                || $hookRequest['type'] !== '$incident.confirmed') {
                throw new \Exception('wrong hook type');
            }

            if (!isset($hookRequest['data']) || !isset($hookRequest['data']['user_id'])) {
                throw new \Exception('no data or user id');
            }

            $user = (new UserRepository())->findById($hookRequest['data']['user_id']);

            event(new AccountCompromised($user));

        } catch (ModelNotFoundException $e) {
            //Cant do anything about it!
            Log::info(__METHOD__, ['exception' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
        }

        return $hookRequest['type'];
    }

    /**
     * @param Request $request
     * @return string
     */
    public function review(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            throw ValidationException::withMessages([
                'request' => ['castle signature not valid']
                ]);
        }

        $hookRequest = json_decode($request->getContent(), true);

        try {
            if (!isset($hookRequest['type'])
                || $hookRequest['type'] !== '$review.opened') {
                throw new \Exception('wrong hookrequest type' . $hookRequest['type'] ?? 'no-type-sent');
            }

            if (!isset($hookRequest['data']) || !isset($hookRequest['data']['user_id'])) {
                throw new \Exception('no data or user id');
            }

            if (!isset($hookRequest['data'])
                || !isset($hookRequest['data']['device_token'])) {
                throw new \Exception('no device token sent');
            }

            $user = (new UserRepository())->findById($hookRequest['data']['user_id']);

            event(new AccountNeedsReview($user, $hookRequest['data']['device_token']));

        } catch (ModelNotFoundException $e) {
            //Cant do anything about it!
            Log::warning(__METHOD__, ['exception' => $e->getMessage()]);
        } catch (\Exception $e) {
            //let's find out what other problems we can get!
            Log::debug(__METHOD__, ['exception' => $e->getMessage()]);
        }

        return $hookRequest['type'];
    }

    /**
     * @param Request $request
     */
    protected function verifyWebhook($request)
	{
        if (app('env') === 'testing') {
            return true;
        }

        $calculated_hmac = base64_encode(hash_hmac('sha256',
                                                   file_get_contents('php://input'),
                                                   config('laracastle.castle.secret'),
                                                   true));

        return hash_equals($request->header('x-castle-signature'), $calculated_hmac);
	}
}
