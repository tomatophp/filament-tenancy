<?php

namespace TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource;
use TomatoPHP\FilamentTenancy\Models\Tenant;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open')
                ->label(trans('filament-tenancy::messages.actions.view'))
                ->icon('heroicon-s-link')
                ->url(fn ($record) => request()->getScheme().'://'.$record->domains()->first()?->domain.'.'.config('filament-tenancy.central_domain').'/'.filament('filament-tenancy')->panel)
                ->openUrlInNewTab(),
            DeleteAction::make()
                ->icon('heroicon-s-trash')
                ->label(trans('filament-tenancy::messages.actions.delete')),
        ];
    }

    /**
     * Keep the tenant owner in the tenant database in sync, matched on the email before the change.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Tenant $tenant */
        $tenant = $this->getRecord();

        $user = [
            'name' => $data['name'],
            'email' => $data['email'],
            'updated_at' => now(),
        ];

        if (filled($data['password'] ?? null)) {
            $user['password'] = $data['password'];
        }

        $tenant->run(function () use ($tenant, $user): void {
            $match = ['email' => $tenant->email];

            if (config('filament-tenancy.single_database')) {
                $user['tenant_id'] = $tenant->getTenantKey();
                $match['tenant_id'] = $tenant->getTenantKey();
            }

            DB::table('users')->updateOrInsert($match, $user);
        });

        return $data;
    }
}
