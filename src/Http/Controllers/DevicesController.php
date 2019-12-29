<?php
namespace robrogers3\Laracastle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use robrogers3\Laracastle\Facades\Laracastle;
use robrogers3\Laracastle\Events\AccountCompromised;
use robrogers3\Laracastle\Repositories\DeviceRepository;
use robrogers3\Laracastle\Repositories\UserRepository;

class DevicesController extends Controller
{
    public function show($user_id, $token)
    {

        if (auth()->user()->id != $user_id) {
            return abort(401);
        }

        $device = (new DeviceRepository($token))->getDevice();

        return view('robrogers3::pages.device', compact('device'));
    }

    /**
     * @param Request request
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'token' => 'required'
        ]);

        session()->flash('status', 'Device Reported.');

        //Event it??
        Laracastle::report($validatedData['token']);

        return redirect('/home');
    }

    /**
     * @param Request $request
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'token' => 'required'
        ]);

        //Event it??
        Laracastle::approve($validatedData['token']);

        session()->put('status', 'Device Approved');

        return redirect('/home');
    }
}
