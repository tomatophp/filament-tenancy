<?php

namespace Tomatophp\FilamentTenancy;

use Illuminate\Support\ServiceProvider;


class FilamentTenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Register generate command
        $this->commands([
           \Tomatophp\FilamentTenancy\Console\FilamentTenancyInstall::class,
        ]);
 
        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/filament-tenancy.php', 'filament-tenancy');
 
        //Publish Config
        $this->publishes([
           __DIR__.'/../config/filament-tenancy.php' => config_path('filament-tenancy.php'),
        ], 'filament-tenancy-config');
 
        //Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
 
        //Publish Migrations
        $this->publishes([
           __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-tenancy-migrations');
        //Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-tenancy');
 
        //Publish Views
        $this->publishes([
           __DIR__.'/../resources/views' => resource_path('views/vendor/filament-tenancy'),
        ], 'filament-tenancy-views');
 
        //Register Langs
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-tenancy');
 
        //Publish Lang
        $this->publishes([
           __DIR__.'/../resources/lang' => base_path('lang/vendor/filament-tenancy'),
        ], 'filament-tenancy-lang');
 
        //Register Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
 
    }

    public function boot(): void
    {
        //you boot methods here
    }
}
