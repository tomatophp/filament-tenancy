<?php

namespace TomatoPHP\FilamentTenancy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use TomatoPHP\FilamentTenancy\Database\Factories\TenantFactory;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;
    use HasFactory;

    protected $fillable = [
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

    public function social(): HasMany
    {
        return $this->hasMany(SocialAuth::class, 'tenant_id', 'id');
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }
}
