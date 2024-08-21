<?php

namespace TomatoPHP\FilamentTenancy\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Features\UserImpersonation;

class LoginUrl extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'token' => "required|string",
            'email' => "required|string|email|max:255",
        ]);

        $tenant = \TomatoPHP\FilamentTenancy\Models\Tenant::query()->where('email', $request->get('email'))->first();
        if($tenant){
            $user =  \App\Models\User::query()->where('email', $tenant->email)->first();
            if($user){
                $user->update([
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'packages' => $tenant->packages,
                    'password' => $tenant->password,
                ]);

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

                if($tenant->name){
                    $site = new \TomatoPHP\FilamentSettingsHub\Settings\SitesSettings();
                    $site->site_name = $tenant->name;
                    $site->save();
                }
            }
        }

        return UserImpersonation::makeResponse($request->get('token'));
    }

    public function generatePermissions(string $table)
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
