![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/3x1io-tomato-tenancy.jpg)

# Filament Tenancy

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-tenancy/version.svg)](https://packagist.org/packages/tomatophp/filament-tenancy)
[![License](https://poser.pugx.org/tomatophp/filament-tenancy/license.svg)](https://packagist.org/packages/tomatophp/filament-tenancy)
[![Downloads](https://poser.pugx.org/tomatophp/filament-tenancy/d/total.svg)](https://packagist.org/packages/tomatophp/filament-tenancy)

Tenancy multi-database integration for FilamentPHP

> [!WARNING]  
> We have a known problem with `route:cache` if you find a problem with multi-database connection from your tenant side you may face this problem just use `php artisan route:clear` and it will be fixed.

## Screenshots

![Tenants](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/tenants-light.png)
![Tenants Dark](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/tenants-dark.png)
![Create](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/create-light.png)
![Edit](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/edit-dark.png)
![Password](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/password-light.png)
![Password Dark](https://raw.githubusercontent.com/tomatophp/filament-tenancy/master/arts/password-dark.png)

## Features

- [x] Multi Database
- [x] Create Tenant Resource
- [x] Sync Tenant Resource
- [x] Password Change
- [x] Tenant Impersonate
- [ ] Share Tenant Data
- [ ] Custom Theme For Tenant
- [ ] Livewire Component For Register New Tenant

## Version Compatibility

| Plugin | Filament | Laravel | PHP |
|--------|----------|---------|-----|
| 1.x (`v3` branch) | 3.x | 10.x \| 11.x | 8.1+ |
| 5.x | 5.x | 12.x \| 13.x | 8.2+ |

## Installation

```bash
composer require tomatophp/filament-tenancy
```
after install your package please run this command

```bash
php artisan filament-tenancy:install
```

in your `.env` add this

```.env
CENTRAL_DOMAIN=tomatophp.test
```

where `tomatophp.test` is your central domain, and make sure you add a root user or a user have a permission to create database, then in your main central panel add this plugin

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

tenant databases are resolved by stancl/tenancy from `config/tenancy.php` (published by the install command), so you do not need an extra database connection. In multi-database mode the database user must be allowed to create databases.

> [!NOTE]
> Upgrading from 1.x: the `dynamic` connection in `config/database.php` is no longer used, you can remove it.

new tenant databases are created and migrated with the migrations in `database/migrations/tenant`. To seed them, set a tenant seeder in `config/tenancy.php` (your application `DatabaseSeeder` is not run inside tenants)

```php
'seeder_parameters' => [
    '--class' => \Database\Seeders\TenantSeeder::class,
    '--force' => true,
],
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

## Other Filament Packages

Checkout our [Awesome TomatoPHP](https://github.com/tomatophp/awesome)
