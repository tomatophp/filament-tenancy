<?php

namespace TomatoPHP\FilamentTenancy;

use Filament\Contracts\Plugin;
use Filament\Panel;
use TomatoPHP\FilamentTenancy\Http\Middleware\ApplyPanelColorsMiddleware;
use TomatoPHP\FilamentTenancy\Http\Middleware\RedirectIfInertiaMiddleware;

class FilamentTenancyPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-tenancy-app';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->middleware([
                RedirectIfInertiaMiddleware::class,
                ApplyPanelColorsMiddleware::class,
            ])
            ->persistentMiddleware(['universal'])
            ->domains(config('filament-tenancy.central_domain'));
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
