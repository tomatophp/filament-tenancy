<?php

namespace TomatoPHP\FilamentTenancy\Support;

use TomatoPHP\FilamentTenancy\Models\Team;

class Framework
{
    public function defaultTeam(): ?Team
    {
        return Team::query()->whereCode('DEFAULT')->first();
    }
}
