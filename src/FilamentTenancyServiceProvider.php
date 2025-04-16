<?php

namespace TomatoPHP\FilamentTenancy;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events\SyncedResourceChangedInForeignDatabase;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use TomatoPHP\FilamentTenancy\Macros\FrameworkColumns;
use TomatoPHP\FilamentTenancy\View\Components\ApplicationLogo;

class FilamentTenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';
    const TENANCY_IDENTIFICATION = Middleware\InitializeTenancyByDomain::class;

    /**
     * @return array
     */
    public function databaseEvents(): array
    {
        return [
            // Tenant events
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    Jobs\SeedDatabase::class,

                    // Your own jobs to prepare the tenant.
                    // Provision API keys, create S3 buckets, anything you want!

                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],
        ];
    }

    public function defaultEvents()
    {
        return [
            // Tenancy events
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],
        ];
    }

    public function register(): void
    {
        //Register generate command
        $this->commands([
           \TomatoPHP\FilamentTenancy\Console\FilamentTenancyInstall::class,
        ]);

        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/filament-tenancy.php', 'filament-tenancy');

        //Publish Config
        $this->publishes([
           __DIR__.'/../config/filament-tenancy.php' => config_path('filament-tenancy.php'),
        ], 'filament-tenancy-config');

        if (!config('filament-tenancy.single_database')) {
            //Register Migrations
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            //Publish Migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'filament-tenancy-migrations');
        }

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
//        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    }

    public function boot(): void
    {
        $this->bootEvents();
        $this->mapRoutes();

        $this->makeTenancyMiddlewareHighestPriority();
        $this->modifyStaticConfigs();
        $this->prepareLivewireForTenancy();

        FrameworkColumns::registerMacros();

        $this->loadViewComponentsAs('tomato', [
            ApplicationLogo::class
        ]);
    }

    protected function bootEvents()
    {
        $events = !config('filament-tenancy.single_database', false)
            ? array_merge($this->databaseEvents(), $this->defaultEvents())
            : $this->defaultEvents();


        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }

    private function prepareLivewireForTenancy(): void
    {
        if(request()->host() !== config('filament-tenancy.central_domain')){

            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/livewire/update', $handle)
                    ->middleware(
                        [
                            'web',
                            'universal',
                            static::TENANCY_IDENTIFICATION,
                        ])->name('livewire.update');
            });
        }
    }

    private function modifyStaticConfigs(): void
    {
        Middleware\InitializeTenancyBySubdomain::$onFail = function ($e) {
            return redirect(config('app.url'));
        };
    }
}
