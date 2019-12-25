# laracastle

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]

Laracastle is a package that automates the installation and configuration of [castle.io](https::castle.io) for your Laravel site.

## What's Castle.io

The short story is that [castle.io](https:/castle.io) procatively protects you users from account hacking.  When you subscribe to their service, they make intelligent decisions when users attempt to login to your site or access protected resources.

For more information, I recommend checking out their [site](https://castle.io).

## Why Laracastle?

[Castle.io](https:/castle.io) is not terribly difficult to integrate, but it does take some work. With this package,  you can integrate [castle.io](https:/castle.io) services within minutes instead of hours.

## How It (Laracastle) Works?

Laracastle hooks into several events dispatched by Laravel related to the user authentication processes. Like: Logging In, Logging Out, and Resetting Passwords. Most important is the Login Event. 

When the Login Event is fired, Lacastle makes a realtime request to [castle.io](https:/castle.io) to determine if the request looks 'fishy' or 'kosher'. And depending on the level of fishiness, it can either Allow the login, Challenge the login, or Deny the Login.

If the Login is allowed, then Laracastle proceeds as per usual.

If the Login is challenged, then we either ask the user reconfrim their email address or ask them to login again. (See [config](#Configuration) )

If the Login is denied, then we disallow Login, and Laravel will take over to lock the account for a specified duration. [Learn more about throttling requests](https://laravel.com/docs/6.x/authentication#login-throttling) on Laravel.com.

## Installation

Via Composer

``` bash
$ composer require robrogers3/laracastle
```

## Configuration

### Configuring Laracastle

#### Initial Configuration

After use have required the package via composer, then run:

```
php artisan vendor:publish --provider='robrogers3\laracastle\LaracastleServiceProvider'
```
Next add this line to your main layouts blade file (e.g. app.blade.php) in the head section:

''''
    @include('vendor/robrogers3/headscript/laracastle')

''''
If you don't know your APP_ID or SECRET, then you need to sign up for [castle.io](https:/castle.io).

Last, update update your .env files, like so:
```
CASTLE_SECRET=YOUR_CASTLE_SECRET
CASTLE_APP_ID=YOUR_CASTLE_APP_ID
CASTLE_MODE=[evaluation|production]
```

When you are just starting out, set the CASTLE_MODE to 'evaluation'. Once you are ready to take action, change the CASTLE_MODE to 'production'.

#### Recommended Configuration Changes

Using Email verification will greatly reduce your headaches!

By default on a Castle challenge response, Laracastle will ask your user to login again, which is lame. A better alternative is just verify their email address.

If you learn how to make this work with Laravel, you will save a lot of trouble,for you.

To start, first learn about [Laravel's Email Verification](https://laravel.com/docs/master/verification).

Next update your Auth routes in routes/web.php like so:
```
Auth::routes(['verify' => true]);
```

Then make sure you user implements 'MustVerifyEmail'

```
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    //...
}
```

Then protect routes that should be verified by email. Like:

```
Route::get('home', function () {
    // Only verified users may enter...
})->middleware('verified'); // verified middleware is the key!
```

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

- [author name][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/robrogers3/laracastle.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/robrogers3/laracastle.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/robrogers3/laracastle/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/robrogers3/laracastle
[link-downloads]: https://packagist.org/packages/robrogers3/laracastle
[link-travis]: https://travis-ci.org/robrogers3/laracastle
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/robrogers3
[link-contributors]: ../../contributors
