<?php

namespace robrogers3\Laracastle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use robrogers3\Laracastle\Facades\Laracastle;
use robrogers3\Laracastle\Events\AccountCompromised;
use robrogers3\Laracastle\Repositories\DeviceRepositoryInterface;

class DevicesController extends Controller
{
    public function __construct(DeviceRepositoryInterface $deviceRepository)
    {
        $this->deviceRepository = $deviceRepository;
    }

    /**
     * @param string $user_id
     * @param string $token
     */
    public function show($user_id, $token)
    {
        if (auth()->user()->id != $user_id) {
            return abort(401);
        }

        $device = $this->deviceRepository->setToken($token)->getDevice();

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
