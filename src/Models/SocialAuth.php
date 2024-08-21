<?php

namespace TomatoPHP\FilamentTenancy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TomatoPHP\FilamentTenancy\Models\Tenant;

class SocialAuth extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'provider',
        'provider_id'
    ];

    /**
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
