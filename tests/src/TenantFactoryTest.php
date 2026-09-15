<?php

use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;
use TomatoPHP\FilamentTenancy\Models\Tenant;

it('ships a tenant factory that also creates the tenant domain', function () {
    Event::fake([TenantCreated::class]);

    $tenant = Tenant::factory()->create(['id' => 'factory_tenant']);

    expect($tenant->exists)->toBeTrue()
        ->and($tenant->getTenantKey())->toBe('factory_tenant')
        ->and($tenant->is_active)->toBeTrue()
        ->and($tenant->domains()->pluck('domain')->all())->toBe(['factory-tenant']);
});
