<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\CreateTenant;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\EditTenant;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\ListTenants;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\ViewTenant;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\RelationManagers\DomainsRelationManager;
use TomatoPHP\FilamentTenancy\FilamentTenancyPlugin;
use TomatoPHP\FilamentTenancy\Models\Tenant;
use TomatoPHP\FilamentTenancy\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

function makeTenant(string $id = 'acme', string $domain = 'acme'): Tenant
{
    $tenant = Tenant::query()->create([
        'id' => $id,
        'name' => ucfirst($id),
        'email' => $id.'@example.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

beforeEach(function () {
    Event::fake([TenantCreated::class, TenantDeleted::class]);

    actingAs(User::factory()->create());
});

it('registers the plugin and the tenant resource on the panel', function () {
    $panel = Filament::getPanel('admin');

    expect($panel->getPlugin('filament-tenancy'))->toBeInstanceOf(FilamentTenancyPlugin::class)
        ->and($panel->getResources())->toContain(TenantResource::class);
});

it('lists tenants', function () {
    $tenants = collect([makeTenant('acme'), makeTenant('globex', 'globex')]);

    livewire(ListTenants::class)
        ->loadTable()
        ->assertCanSeeTableRecords($tenants)
        ->assertSee('acme.localhost');
});

it('renders the create page', function () {
    livewire(CreateTenant::class)->assertSuccessful();
});

it('validates the create form', function () {
    livewire(CreateTenant::class)
        ->fillForm([
            'name' => '',
            'id' => '',
            'domain' => '',
            'email' => 'not-an-email',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'id' => 'required', 'domain' => 'required', 'email' => 'email']);
});

it('renders the view page', function () {
    $tenant = makeTenant();

    livewire(ViewTenant::class, ['record' => $tenant->getRouteKey()])->assertSuccessful();
});

it('renders the edit page with the tenant data', function () {
    $tenant = makeTenant();

    livewire(EditTenant::class, ['record' => $tenant->getRouteKey()])
        ->assertSuccessful()
        ->assertSchemaStateSet([
            'name' => 'Acme',
            'email' => 'acme@example.com',
        ]);
});

it('lists the tenant domains in the relation manager', function () {
    $tenant = makeTenant();

    livewire(DomainsRelationManager::class, [
        'ownerRecord' => $tenant,
        'pageClass' => EditTenant::class,
    ])
        ->assertSuccessful()
        ->assertSee('acme');
});
