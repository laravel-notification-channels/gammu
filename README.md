# Gammu Notifications Channel for Laravel 5.3

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/gammu.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/gammu)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/laravel-notification-channels/gammu.svg?branch=master)](https://travis-ci.org/laravel-notification-channels/gammu)
[![StyleCI](https://styleci.io/repos/67163908/shield)](https://styleci.io/repos/67163908)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/cb9970f9-6d96-4412-b4bb-d93a43daac40.svg?style=flat-square)](https://insight.sensiolabs.com/projects/cb9970f9-6d96-4412-b4bb-d93a43daac40)
[![Quality Score](https://img.shields.io/scrutinizer/g/laravel-notification-channels/gammu.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/gammu)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/gammu.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/gammu)

This package makes it easy to send SMS notifications using [Gammu SMSD](https://wammu.eu/smsd/) with Laravel 5.3.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
	- [Setting Up Gammu Service](#setting-up-gammu-service)
	    - [Using Native Gammu SMSD Method](#using-native-gammu-smsd-method)
	    - [Using Gammu Api](#using-gammu-api)
- [Usage](#usage)
    - [Routing a message](#routing-a-message)
	- [Available Message methods](#available-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Requirements

### Gammu

Make sure your Gammu SMSD has properly configured and able to send SMS. For more info to install and configure Gammu SMSD, read the [Gammu SMSD documentation](https://wammu.eu/smsd/).

### Gammu Api

This is optional if you want to use [Gammu Api](https://github.com/kristiandrucker/gammuApi). Make sure Gammu Api has properly configured and able to send SMS using this API.

Under the hood, Gammu Api is using `gammu sendsms` command line.

## Installation

You can install the package via composer:

```console
composer require laravel-notification-channels/gammu
```

You must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    NotificationChannels\Gammu\GammuServiceProvider::class,
],
```

### Setting Up Gammu Service

There are two methods to send SMS using Gammu. First method is using native Gammu SMSD method, by inserting data directly to Gammu SMSD database. The second method is using Gammu Api.

#### Using Native Gammu SMSD Method

Make sure your Gammu SMSD has properly configured and able to send SMS by inserting data to `outbox` table. The Gammu SMSD and  database can be installed in the same machine or in different machine.

Add this settings in `config/services.php` to send SMS using native Gammu method.

```php
...
'gammu' => [
    'method' => env('GAMMU_METHOD', 'db'),
    'sender' => env('GAMMU_SENDER', null),
],
...
``` 

Set the database setting to point Gammu SMSD database in `config/database.php` by adding this settings below.

```php
// config/database.php
...
'connections' => [
    ...
    'gammu' => [
        'driver' => 'mysql',
        'host' => env('DB_GAMMU_HOST', 'localhost'),
        'port' => env('DB_GAMMU_PORT', '3306'),
        'database' => env('DB_GAMMU_DATABASE', 'forge'),
        'username' => env('DB_GAMMU_USERNAME', 'forge'),
        'password' => env('DB_GAMMU_PASSWORD', ''),
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
    ...
],
...
```

The sender is the default sender name defined in `phones` table. If it's not set, it will automatically select the first data from `phones` table. This setting is useful if you have multiple sender.

#### Using Gammu Api

Make sure your Gammu Api has properly configured and able to send SMS via it's API. The Gammu Api can be installed in the same machine or in different machine.

Add these settings in `config/services.php` to send SMS.

``` php
...
'gammu' => [
    'method' => env('GAMMU_METHOD', 'api'),
    'auth' => env('GAMMU_AUTH', 'gammu-api-key'),
    'url' => env('GAMMU_URL', 'http://gammu.api/')
],
...
```

### Using Gammu Api with Redis

Make sure, that your Redis server is running and it's able to communicate with your application and gammu api. You are also able to specify ```->channel('name-channel')``` for using multiple Gammu Api's or have it on a non-default channel.

``` php
...
'gammu' => [
    'method' => env('GAMMU_METHOD', 'redis')
],
...
```

## Usage

You can now use the channel in your `via()` method inside the Notification class.

```php
namespace App\Notifications;

use NotificationChannels\Gammu\GammuChannel;
use NotificationChannels\Gammu\GammuMessage;
use Illuminate\Notifications\Notification;

class SendNotificationToSms extends Notification
{
    public function via($notifiable)
    {
        return [GammuChannel::class];
    }

    public function toGammu($notifiable)
    {
        return (new GammuMessage())
            ->to($phoneNumber)
            ->content($content);
    }
}
```

If you have multiple senders, you can set the sender by passing `sender` method. If sender is not set, it will use one of sender from `phones` table. This method is only available if you're using native Gammu SMSD method.

```php
public function toGammu($notifiable)
{
    return (new GammuMessage())
        ->to($phoneNumber)
        ->sender($sender)
        ->content($content);
}
```

### Routing a message

You can either send the notification by providing with the phone number of the recipient to the `to($phoneNumber)` method like shown in the above example or add a `routeNotificationForGammu()` method in your notifiable model.

```php
...
/**
 * Route notifications for the Gammu channel.
 *
 * @return string
 */
public function routeNotificationForGammu()
{
    return $this->phone;
}
...
```

### Available methods

* `to($phoneNumber)` : `(string)` Receiver phone number. Using international phone number (+62XXXXXXXXXX) format is highly suggested.
* `content($message)` : `(string)` The SMS content. If content length is more than 160 characters, it will be sent as [long SMS](https://en.wikipedia.org/wiki/Concatenated_SMS) automatically.
* `sender($sender)` : `(string)` Phone sender ID set in Gammu `phones` table. This method is only available if you're using native Gammu method.
* `callback($callbackText)` : `(string)` Callback text for [Gammu Api](https://github.com/kristiandrucker/gammuApi) gives you function to pass text and when the Api will send your message it will be sent back to your callback url specified in your Gammu Api. Please setup callback properly on your Gammu Api.
* `channel($redisChannelName)` : `(string)` Channel to publish to Redis. Default channel is `gammu-channel`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
$ composer test
```

## Security

If you discover any security related issues, please email halo@matriphe.com or kristian@rolmi.sk instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Muhammad Zamroni](https://github.com/matriphe)
- [Kristian Drucker](https://github.com/kristiandrucker)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
