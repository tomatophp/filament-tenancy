<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\artisan;

function publishedTenancyPaths(): array
{
    return [
        'config' => config_path('tenancy.php'),
        'routes' => base_path('routes/tenant.php'),
        'migrations' => database_path('migrations/tenant'),
    ];
}

afterEach(function () {
    File::delete(publishedTenancyPaths()['config']);
    File::delete(publishedTenancyPaths()['routes']);
    File::deleteDirectory(publishedTenancyPaths()['migrations']);
    File::deleteDirectory(resource_path('views/components/layouts'));
});

it('registers the install command', function () {
    expect(Artisan::all())->toHaveKey('filament-tenancy:install');
});

it('runs the install command in single database mode and publishes the tenancy files', function () {
    artisan('filament-tenancy:install', ['--single' => true])->assertSuccessful();

    $paths = publishedTenancyPaths();

    expect(Schema::hasTable('tenants'))->toBeTrue()
        ->and(Schema::hasTable('domains'))->toBeTrue()
        ->and(File::exists($paths['config']))->toBeTrue()
        ->and(File::get($paths['config']))->toBe(File::get(__DIR__.'/../../publish/config/single.tenancy.php'))
        ->and(File::exists($paths['routes']))->toBeTrue()
        ->and(File::exists($paths['migrations'].'/0001_01_01_000000_create_users_table.php'))->toBeTrue();
});
