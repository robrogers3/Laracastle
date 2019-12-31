# Laracastle

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]

Laracastle is a package that automates the installation and configuration of [castle.io](https::castle.io) for your Laravel site.

## What's Castle.io

The short story is that [castle.io](https:/castle.io) procatively protects you users from account hacking.  When you subscribe to their service, they make intelligent decisions when users attempt to login to your site or access protected resources.

For more information, I recommend checking out their [site](https://castle.io).

## Why Laracastle?

[Castle.io](https:/castle.io) is not terribly difficult to integrate, but it does take some work. 

With this package,  you can integrate [castle.io](https:/castle.io) services within minutes instead of hours or even days.

## Installation

Via Composer

``` bash
$ composer require robrogers3/laracastle
```

## Configuration

### Requirements

Laracastle pretty much depends on the [Laravel Auth](https://laravel.com/docs/6.x/authentication). On Laravel 6, auth is a separate package. So first install it.

```
composer require laravel/ui --dev

```

Then do one of these:
```
php artisan ui bootstrap --auth
# or 
php artisan ui vue --auth
# or
php artisan ui react --auth
```

And, then of course run:
```
php artisan migrate
```


### Initial Configuration

After you have required the package via composer, run:

```
php artisan vendor:publish --provider='robrogers3\laracastle\LaracastleServiceProvider'
```
Next add this line to your main layouts blade file (e.g. app.blade.php) in the head section:

```
    @include('vendor/robrogers3/headscript/laracastle')

```
If you don't know your castle.io APP_ID or SECRET, then you need to sign up for [castle.io](https:/castle.io).

Last, update update your .env files, like so:
```
CASTLE_SECRET=YOUR_CASTLE_SECRET
CASTLE_APP_ID=YOUR_CASTLE_APP_ID
CASTLE_MODE=[evaluation|production]
```

*When you are just starting out, set the CASTLE_MODE to 'evaluation'. Once you are ready to take action, change the CASTLE_MODE to 'production'.*

### (Highly) Recommended Configuration Changes

Use "Email Verification" to protected your routes to greatly reduce your headaches!

**NOTE default action on challenge is not good, as it will still return challenge, I think**

By default, if [castle.io](https://castle.io) challenges a login attempt then Laracastle will ask your user to login again, which can be a pain. A better alternative is to ensure users have verified their email address via the MustVerifyEmail interface.

 To start, first learn about [Laravel's Email Verification](https://laravel.com/docs/master/verification).

Next update your Auth routes in routes/web.php like so:

```
Auth::routes(['verify' => true]);
```
Then **make sure** you user implements 'MustVerifyEmail' and 'Laracastle\UserIntefrace'.

You will also need to add these two traits to your user model:
* ResetsAccounts, and
* ChecksVerification

Your User class will look like this:
```
use robrogers3\Laracastle\UserInterface;
use robrogers3\Laracastle\Traits\ChecksVerification;
use robrogers3\Laracastle\Traits\ResetsAccount;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail, UserInterface
{
    use Notifiable, ResetsAccount, ChecksVerification;
    //...
}
    use Notifiable;
    //...
}
```

Lastly, protect routes that should be verified by email. Like:

```
Route::get('home', function () {
    // Only verified users may enter...
})->middleware('verified'); // verified middleware is the key!
```

Optional: Add this to your AppServiceProvider
```
//...
use robrogers3\Laracastle\UserInterface;
//...

public function boot()
{
    $this->app->bind(UserInterface::class, function ($app) {
            return User::class;
    });
}
```

### When you are ready to go live.

First head over to [Web Hooks on your Castle.io Dashboard](https://dashboard.castle.io/settings/webhooks).

And set two webhook end points:

1. For the '$incident.confirmed' event add this endpoint:
```
https://your-base-url.com/laracastle/compromised-webhook
```

2. For the '$review.opened' event add this endpoint:
```
https://your-base-url.com/laracastle/review-webhook
```

Do NOT select **Subscribe to All Events** for either endpoint.

*Note the second webhook is recommended but optional.*

Congrats your done. Your users are now protected by [castle.io])(https://castle.io)

## How It (Laracastle) Works?

### Protecting Your User Accounts On Login

Laracastle hooks into several events dispatched by Laravel related to the user authentication processe. Like: Logging In, Logging Out, and Resetting Passwords. Most important is the Login Event. 

When the Login Event is fired, Lacastle makes a realtime request to [castle.io](https:/castle.io) to determine if the request looks 'fishy' or 'kosher'. And depending on the level of fishiness, it can either Allow the login, Challenge the login, or Deny the Login.

If the Login is allowed, then Laracastle proceeds as per usual.

If the Login is challenged, then we either ask the user reconfrim their email address, or ask them to login again. (See [config](#Configuration) )

If the Login is denied, then we disallow Login, and then Laravel will take over to lock the account for a specified duration. [Learn more about throttling requests](https://laravel.com/docs/6.x/authentication#login-throttling) on Laravel.com.

### Proactively Protecting Your Accounts with Webhooks

#### When your account may have been compromised.

If Castle.io determines that an account or device may have been compromised, it sends a request to a webhook in Laracastle. Laracastle uses this information to reset the user's account password, and then notify them via email that their account may have been compromised and that they need to reset their password before they can access their account.

#### When unusal or suspicious devices access your account.

When castle.io believes their has been unusual or suspicious device activity accessing your account, it sends another webhook to Laracastle. Laracastle uses this information to notify the user of the activity, and ask them review it.

On clicking 'Review Device' from the notification, they are able to see the details of the activity. The user can either confirm it was valid actity, or report it as invalid. If it is valid, the suspicious activity is resolved, otherwise, the activity is escalated. When escalated the compromised webhook will be run, the account password will be reset, and the user will be notified via email.

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Rob Rogers][link-author]

## License

[MIT License](LICENSE)

[ico-version]: https://img.shields.io/packagist/v/robrogers3/Laracastle.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/robrogers3/Laracastle.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/robrogers3/Laracastle/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/robrogers3/Laracastle
[link-downloads]: https://packagist.org/packages/robrogers3/Laracastle
[link-travis]: https://travis-ci.org/robrogers3/Laracastle
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/robrogers3
[link-contributors]: ../../contributors
