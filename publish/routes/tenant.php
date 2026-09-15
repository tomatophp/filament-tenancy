<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TomatoPHP\FilamentTenancy\FilamentTenancyServiceProvider;
use TomatoPHP\FilamentTenancy\Http\Controllers\LoginUrl;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    'universal',
    FilamentTenancyServiceProvider::TENANCY_IDENTIFICATION,
])->group(function () {
    if (config('filament-tenancy.features.impersonation')) {
        Route::get('/login/url', [LoginUrl::class, 'index']);
    }

    // Your Tenant routes here

});
