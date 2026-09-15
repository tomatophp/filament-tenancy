<?php

use Illuminate\Support\Facades\Route;

it('redirects the register components only to routes the package defines (#5)', function () {
    Route::group([], __DIR__.'/../../routes/web.php');
    Route::getRoutes()->refreshNameLookups();

    $source = file_get_contents(__DIR__.'/../../src/Livewire/RegisterDemo.php')
        .file_get_contents(__DIR__.'/../../src/Livewire/RegisterOtp.php');

    preg_match_all("/->route\\('([^']+)'/", $source, $matches);

    expect($matches[1])->not->toBeEmpty();

    foreach (array_unique($matches[1]) as $name) {
        expect(Route::has($name))->toBeTrue("Route [{$name}] is used by the register components but not defined in routes/web.php");
    }
});
