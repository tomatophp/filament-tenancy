<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\CreateTenant;
use TomatoPHP\FilamentTenancy\Models\Tenant;
use TomatoPHP\FilamentTenancy\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    config()->set('tenancy.migration_parameters', [
        '--force' => true,
        '--path' => [realpath(__DIR__.'/../../publish/database/migrations/tenant')],
        '--realpath' => true,
    ]);

    actingAs(User::factory()->create());
});

afterEach(function () {
    tenancy()->end();

    foreach (File::glob(database_path(config('tenancy.database.prefix').'*')) as $file) {
        File::delete($file);
    }
});

it('creates a tenant without running the application DatabaseSeeder when no tenant seeder is configured', function () {
    config()->set('tenancy.seeder_parameters', []);

    livewire(CreateTenant::class)
        ->fillForm([
            'name' => 'Initech',
            'id' => 'initech',
            'domain' => 'initech',
            'email' => 'owner@initech.test',
            'password' => 'Password-123',
            'passwordConfirmation' => 'Password-123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tenant = Tenant::query()->findOrFail('initech');

    expect($tenant->run(fn () => DB::table('users')->where('email', 'owner@initech.test')->exists()))->toBeTrue();
});
