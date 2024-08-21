<?php

namespace TomatoPHP\FilamentTenancy\Filament\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantLogin extends Login
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }


        $permissions = [];
        $packages = config('app.packages');
        foreach ($packages as $key=>$package){
            if(in_array($key, $user->packages)){
                foreach ($package['permissions'] as $permission){
                    $permissions  = array_merge($permissions, $this->generatePermissions($permission));
                }
            }
        }

        $role = Role::query()->where('name', 'super_admin')->first();
        if(!$role){
            $role = Role::query()->create([
                'name' => 'super_admin',
                'guard_name' => 'web',
            ]);
        }

        $role->syncPermissions($permissions);
        $user->roles()->sync($role->id);

        session()->regenerate();

        return app(LoginResponse::class);
    }

    private function generatePermissions(string $table)
    {
        if(str($table)->contains('page')){
            $array = [
                $table
            ];
        }
        else {
            $array = [
                'view_' . $table,
                'view_any_' . $table,
                'create_' . $table,
                'update_' . $table,
                'restore_' . $table,
                'restore_any_' . $table,
                'replicate_' . $table,
                'reorder_' . $table,
                'delete_' . $table,
                'delete_any_' . $table,
                'force_delete_' . $table,
                'force_delete_any_' . $table,
            ];

        }

        $permissionsIds=[];
        foreach ($array as $value) {
            $check = Permission::query()->where('name', $value)->first();
            if(!$check){
                $getId = Permission::query()->create([
                    'name' => $value,
                    'guard_name' => 'web',
                ]);

                $permissionsIds[] = $getId->id;
            }
            else {
                $permissionsIds[] = $check->id;
            }
        }

        return $permissionsIds;
    }

}
