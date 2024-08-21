<?php

namespace TomatoPHP\FilamentTenancy\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use TomatoPHP\FilamentTenancy\Models\SocialAuth;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends \Stancl\Tenancy\Database\Models\Tenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'otp_code',
        'otp_code_active_at',
        'is_active',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'password',
            'otp_code',
            'otp_code_active_at',
            'is_active',
            'data',
        ];
    }

    /**
     * @return HasMany
     */
    public function social(): HasMany
    {
        return $this->hasMany(SocialAuth::class, 'tenant_id', 'id');
    }
}
