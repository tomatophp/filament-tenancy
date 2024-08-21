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
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        config(['database.connections.dynamic.database' => config('tenancy.database.prefix').$record->id. config('tenancy.database.suffix')]);
        DB::connection('dynamic')
            ->table('users')
            ->where('email', $record->email)
            ->update([
                "name" => $data['name'],
                "email" => $data['email'],
                "packages" => json_encode($data['packages']),
                "password" => $data['password'],
            ]);

        return $data;
    }
}
