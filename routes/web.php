<?php


use Illuminate\Support\Facades\Route;


if(config('filament-tenancy.features.homepage')){
    Route::get('/', \TomatoPHP\FilamentTenancy\Livewire\RegisterDemo::class)->name('tenancy.home');
}

if(config('filament-tenancy.features.auth')){
    Route::get('/tenancy/verify-otp', \TomatoPHP\FilamentTenancy\Livewire\RegisterOtp::class)->name('tenancy.verify.otp');

    Route::middleware(['web', 'throttle:10'])->group(function (){
        Route::get('/tenancy/login/{provider}', [\TomatoPHP\FilamentTenancy\Http\Controllers\AuthController::class, 'provider'])->name('tenancy.login.provider');
        Route::get('/tenancy/login/{provider}/callback', [\TomatoPHP\FilamentTenancy\Http\Controllers\AuthController::class, 'callback'])->name('tenancy.login.provider.callback');
    });
}
