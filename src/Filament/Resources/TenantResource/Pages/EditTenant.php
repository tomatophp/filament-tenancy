<?php

namespace TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages;

use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open')
                ->label(trans('filament-tenancy::messages.actions.view'))
                ->icon('heroicon-s-link')
                ->url(fn($record) => request()->getScheme() . "://" . $record->domains()->first()?->domain . '.' . config('filament-tenancy.central_domain') . '/' . filament('filament-tenancy')->panel)
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()
                ->icon('heroicon-s-trash')
                ->label(trans('filament-tenancy::messages.actions.delete')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (isset($data['password'])) {
            $updateData["password"] = $data['password'];
        }

        try {
            if (!config('filament-tenancy.single_database')) {
                $dbName = config('tenancy.database.prefix') . $record->id . config('tenancy.database.suffix');
                config(['database.connections.dynamic.database' => $dbName]);
            }
            DB::purge('dynamic');

            DB::connection('dynamic')->getPdo();
        } catch (\Exception $e) {
            throw new \Exception("Failed to connect to tenant database: {$dbName}");
        }

        $user = DB::connection('dynamic')
            ->table('users')
            ->where('email', $record->email);

        if (config('filament-tenancy.single_database')) {
            $user = $user->where('tenant_id', $record->id);

            $updateData['tenant_id'] = $record->id;
        }

        $user->updateOrInsert(
            [
                'email' => $record->email,
            ],
            $updateData,
        );

        return $data;
    }
}
