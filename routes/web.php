<?php

use Illuminate\Support\Facades\Route;
use TomatoPHP\FilamentTenancy\Http\Controllers\AuthController;
use TomatoPHP\FilamentTenancy\Livewire\RegisterDemo;
use TomatoPHP\FilamentTenancy\Livewire\RegisterOtp;

Route::domain(config('filament-tenancy.central_domain'))->middleware(['web'])->group(function () {
    if (config('filament-tenancy.features.homepage')) {
        Route::get('/', RegisterDemo::class)->name('tenancy.home');
    }

    if (config('filament-tenancy.features.auth')) {
        Route::get('/tenancy/verify-otp', RegisterOtp::class)->name('tenancy.verify.otp');

        Route::middleware(['web', 'throttle:10'])->group(function () {
            Route::get('/tenancy/login/{provider}', [AuthController::class, 'provider'])->name('tenancy.login.provider');
            Route::get('/tenancy/login/{provider}/callback', [AuthController::class, 'callback'])->name('tenancy.login.provider.callback');
        });
    }
});
