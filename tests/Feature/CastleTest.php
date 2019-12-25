<?php

namespace Tests\Feature;

use Mockery;
//use App\User;
use robrogers3\Laracastle\Laracastle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Orchestra\Testbench\TestCase;

class CastleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['robrogers3\Laracastle\LaracastleServiceProvider'];
    }
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
            //$x = resolve('Laracastle')->authenticate($e);
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
    public function test_it_does_castle_stuff()
    {
        $spy = $this->spy(Laracastle::class);
        $user = Mockery::mock(User::class)->makePartial();
        Event::dispatch(new Login('web', $user, false));
        $spy->shouldHaveReceived('authenticate')->once();

    }
}
class User {


}
