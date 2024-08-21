<?php

namespace TomatoPHP\FilamentTenancy\Models;

use App\Models\User;
use TomatoPHP\FilamentTenancy\Concerns\Model\FrameworkTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use FrameworkTraits;
    protected $guarded = [];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'team_user');
    }

    public function getCodePrefix()
    {
        // TODO: Implement getCodePrefix() method.
    }
}
