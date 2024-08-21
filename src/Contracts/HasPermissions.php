<?php

namespace TomatoPHP\FilamentTenancy\Contracts;

interface HasPermissions
{
    public static function getPermissionPrefixes(): array;
}
