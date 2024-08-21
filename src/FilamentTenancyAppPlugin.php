<?php

namespace TomatoPHP\FilamentTenancy;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use TomatoPHP\FilamentTenancy\Filament\Pages\TenantLogin;
use TomatoPHP\FilamentTenancy\Http\Middleware\ApplyPanelColorsMiddleware;
use TomatoPHP\FilamentTenancy\Http\Middleware\RedirectIfInertiaMiddleware;

class FilamentTenancyAppPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-tenancy-app';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->login(TenantLogin::class)
            ->middleware([
                PreventAccessFromCentralDomains::class,
                RedirectIfInertiaMiddleware::class,
            ])
            ->middleware([
                'universal',
                FilamentTenancyServiceProvider::TENANCY_IDENTIFICATION,
                PreventAccessFromCentralDomains::class,
            ], isPersistent: true);

        $domains = tenant()?->domains()->pluck('domain') ?? [];
        $panel->domains($domains);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
