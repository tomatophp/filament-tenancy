<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\CreateTenant;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\EditTenant;
use TomatoPHP\FilamentTenancy\Models\Tenant;
use TomatoPHP\FilamentTenancy\Tests\Models\User;
use TomatoPHP\FilamentTenancy\Tests\TenantSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    config()->set('tenancy.migration_parameters', [
        '--force' => true,
        '--path' => [realpath(__DIR__.'/../../publish/database/migrations/tenant')],
        '--realpath' => true,
    ]);

    config()->set('tenancy.seeder_parameters', [
        '--class' => TenantSeeder::class,
        '--force' => true,
    ]);

    actingAs(User::factory()->create());
});

afterEach(function () {
    tenancy()->end();

    foreach (File::glob(database_path(config('tenancy.database.prefix').'*')) as $file) {
        File::delete($file);
    }
});

function tenantUser(Tenant $tenant): ?object
{
    return $tenant->run(fn () => DB::table('users')->first());
}

it('creates the tenant, its domain and its first user in the tenant database', function () {
    livewire(CreateTenant::class)
        ->fillForm([
            'name' => 'Acme',
            'id' => 'acme',
            'domain' => 'acme',
            'email' => 'owner@acme.test',
            'password' => 'Password-123',
            'passwordConfirmation' => 'Password-123',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tenant = Tenant::query()->findOrFail('acme');

    expect($tenant->domains()->pluck('domain')->all())->toBe(['acme'])
        ->and(tenantUser($tenant))->not->toBeNull()
        ->and(tenantUser($tenant)->email)->toBe('owner@acme.test');
});

it('updates the tenant user when the tenant is edited', function () {
    livewire(CreateTenant::class)
        ->fillForm([
            'name' => 'Acme',
            'id' => 'acme',
            'domain' => 'acme',
            'email' => 'owner@acme.test',
            'password' => 'Password-123',
            'passwordConfirmation' => 'Password-123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tenant = Tenant::query()->findOrFail('acme');

    livewire(EditTenant::class, ['record' => $tenant->getRouteKey()])
        ->fillForm([
            'name' => 'Acme Corp',
            'email' => 'boss@acme.test',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user = tenantUser($tenant->fresh());

    expect($tenant->fresh()->name)->toBe('Acme Corp')
        ->and($user->name)->toBe('Acme Corp')
        ->and($user->email)->toBe('boss@acme.test');
});
