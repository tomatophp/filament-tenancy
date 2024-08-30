![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/3x1io-tomato-tenancy.jpg)

# Filament Tenancy

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-tenancy/version.svg)](https://packagist.org/packages/tomatophp/filament-tenancy)
[![License](https://poser.pugx.org/tomatophp/filament-tenancy/license.svg)](https://packagist.org/packages/tomatophp/filament-tenancy)
[![Downloads](https://poser.pugx.org/tomatophp/filament-tenancy/d/total.svg)](https://packagist.org/packages/tomatophp/filament-tenancy)

Tenancy multi-database integration for FilamentPHP

## Screenshots

![Tenants](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/tenants.png)
![Create](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/create.png)
![Edit](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/edit.png)
![Password](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/password.png)

## Features

- [x] Multi Database
- [x] Create Tenant Resource
- [x] Sync Tenant Resource
- [x] Password Change
- [x] Tenant Impersonate
- [ ] Share Tenant Data
- [ ] Custom Theme For Tenant
- [ ] Livewire Component For Register New Tenant

## Installation

```bash
composer require tomatophp/filament-tenancy
```
after install your package please run this command

```bash
php artisan filament-tenancy:install
```

in your `.env` make sure you add a root user or a user have a permission to create database, then in your main central panel add this plugin

```php
use TomatoPHP\FilamentTenancy\FilamentTenancyPlugin;

->plugin(FilamentTenancyPlugin::make()->panel('app'))

```

now you need to create a panel for tenancy app

```bash
php artisan filament:panel
```

and make the name same as `->panel('app')`, in your tenancy app panel add this plugin

```php

use TomatoPHP\FilamentTenancy\FilamentTenancyAppPlugin;

->plugin(FilamentTenancyAppPlugin::make())
```

now on your `config\database.php` add this code

```php
    ...
    'connections' => [
        'dynamic' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        ...
    ],  
```
now run config:cache

```php
php artisan config:cache
```

on your `bootstrap\app.php` add this middleware

```php
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

->withMiddleware(function (Middleware $middleware) {
    $middleware->group('universal', [
        InitializeTenancyByDomain::class,
        InitializeTenancyBySubdomain::class,
    ]);
})
```

## Allow Impersonate

you can allow impersonate to tanent panel with 1 click by use this method on your plugin

```php

use TomatoPHP\FilamentTenancy\FilamentTenancyPlugin;

->plugin(
    FilamentTenancyPlugin::make()
        ->panel('app')
        ->allowImpersonate()
)
```


## Publish Assets

you can publish config file by use this command

```bash
php artisan vendor:publish --tag="filament-tenancy-config"
```

you can publish views file by use this command

```bash
php artisan vendor:publish --tag="filament-tenancy-views"
```

you can publish languages file by use this command

```bash
php artisan vendor:publish --tag="filament-tenancy-lang"
```

you can publish migrations file by use this command

```bash
php artisan vendor:publish --tag="filament-tenancy-migrations"
```

## Support

you can join our discord server to get support [TomatoPHP](https://discord.gg/Xqmt35Uh)

## Docs

you can check docs of this package on [Docs](https://docs.tomatophp.com/plugins/laravel-package-generator)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY](SECURITY.md) for more information about security.

## Credits

- [Fady Mondy](mailto:info@3x1.io)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
