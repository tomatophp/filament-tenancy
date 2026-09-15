<?php

namespace TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource;
use TomatoPHP\FilamentTenancy\Models\Tenant;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation(collect($data)->except('domain')->toArray());
        $record->domains()->create(['domain' => collect($data)->get('domain')]);

        return $record;
    }

    /**
     * Create the tenant owner inside the tenant context, so stancl resolves the tenant
     * connection for every driver instead of a hand-configured "dynamic" connection.
     */
    protected function afterCreate(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->getRecord();

        $tenant->run(function () use ($tenant): void {
            $user = [
                'name' => $tenant->name,
                'email' => $tenant->email,
                'password' => $tenant->password,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $match = ['email' => $tenant->email];

            if (config('filament-tenancy.single_database')) {
                $user['tenant_id'] = $tenant->getTenantKey();
                $match['tenant_id'] = $tenant->getTenantKey();
            }

            DB::table('users')->updateOrInsert($match, $user);
        });
    }
}
