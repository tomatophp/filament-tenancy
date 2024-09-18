<?php

namespace TomatoPHP\FilamentTenancy;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Nwidart\Modules\Module;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource;
use TomatoPHP\FilamentTenancy\Http\Middleware\ApplyPanelColorsMiddleware;
use TomatoPHP\FilamentTenancy\Http\Middleware\RedirectIfInertiaMiddleware;

class FilamentTenancyPlugin implements Plugin
{
    public string $panel = "app";
    public bool $allowImpersonate = false;
    private bool $isActive = false;

    public function getId(): string
    {
        return 'filament-tenancy';
    }

    public function allowImpersonate(bool $allowImpersonate=true): static
    {
        $this->allowImpersonate = $allowImpersonate;
        return $this;
    }

    public function panel(string $panel): static
    {
        $this->panel = $panel;
        return $this;
    }

    public function register(Panel $panel): void
    {
        if(class_exists(Module::class) && \Nwidart\Modules\Facades\Module::find('FilamentTenancy')?->isEnabled()){
            $this->isActive = true;
        }
        else {
            $this->isActive = true;
        }

        if($this->isActive) {
            $panel
                ->resources([
                    TenantResource::class
                ])
                ->middleware([
                    RedirectIfInertiaMiddleware::class,
                ])
                ->persistentMiddleware(['universal'])
                ->domains([
                    config('filament-tenancy.central_domain')
                ]);
        }
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
