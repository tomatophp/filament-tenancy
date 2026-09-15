<?php

namespace TomatoPHP\FilamentTenancy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stancl\Tenancy\Features\UserImpersonation;
use TomatoPHP\FilamentTenancy\Models\Tenant;

class LoginUrl extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|string|email|max:255',
        ]);

        $tenant = Tenant::query()->where('email', $request->get('email'))->first();
        if ($tenant) {
            $userModel = config('auth.providers.users.model', 'App\\Models\\User');
            $user = $userModel::query()->where('email', $tenant->email)->first();
            if ($user) {
                $user->update([
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'password' => $tenant->password,
                ]);
            }
        }

        return UserImpersonation::makeResponse($request->get('token'));
    }
}
