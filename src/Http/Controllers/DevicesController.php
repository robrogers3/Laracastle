<?php
namespace robrogers3\Laracastle\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use robrogers3\Laracastle\Events\AccountCompromised;
use robrogers3\Laracastle\Repositories\DeviceRepository;
use robrogers3\Laracastle\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DevicesController extends Controller
{
    public function show($user_id, $token)
    {
        try {
            $user = (new UserRepository())->findById($user_id);
        } catch (ModelNotFoundException $e) {
            Log::debug(__METHOD__, ['message' => $e->getMessage()]);
            return abort(401);
        }

        $device = (new DeviceRepository($token))->getDevice();

        return view('robrogers3::pages.device', compact('device'));
    }
}
