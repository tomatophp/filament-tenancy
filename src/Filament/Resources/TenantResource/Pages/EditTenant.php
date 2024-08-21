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
                ->label('Open Tenant')
                ->icon('heroicon-s-link')
                ->url(fn($record) => "https://".$record->domains()->first()?->domain .'.'.config('filament-tenancy.central_domain'). '/' . filament('filament-tenancy')->panel)
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()
                ->icon('heroicon-s-trash')
                ->label('Delete'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        $updateData = [
            "name" => $data['name'],
            "email" => $data['email'],
        ];

        if(isset($data['password'])){
            $updateData["password"] = $data['password'];
        }

        config(['database.connections.dynamic.database' => config('tenancy.database.prefix').$record->id. config('tenancy.database.suffix')]);
        $user = DB::connection('dynamic')
            ->table('users')
            ->where('email', $record->email)
            ->first();
        if($user){
            DB::connection('dynamic')
                ->table('users')
                ->where('email', $record->email)
                ->update([
                    "name" => $data['name'],
                    "email" => $data['email'],
                ]);
        }
        else {
            DB::connection('dynamic')
                ->table('users')
                ->insert($updateData);
        }

        return $data;
    }
}
