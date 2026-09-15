# v5.0.0

- Requires `filament/filament` ^5.0 (Livewire 4)
- Supports Laravel 12 and 13, PHP 8.2+, `stancl/tenancy` ^3.9
- Tenant resource, pages and domains relation manager ported to the Filament v5 schema / actions APIs
- Creating and editing a tenant writes its owner inside `$tenant->run()`, so stancl resolves the tenant database for every driver; the manual `dynamic` connection is no longer needed (remove it from `config/database.php`)
- New tenants are only seeded when `tenancy.seeder_parameters.--class` is set; the application `DatabaseSeeder` (usually central data) no longer runs inside every new tenant and breaks its creation
- The "Unique ID" entered on the create form is kept as the tenant id (a UUID was generated instead)
- The register component redirects to the `tenancy.verify.otp` and `tenancy.login.provider` routes the package defines (#5)
- `filament-tenancy:install` runs the migrations in-process and no longer runs `optimize`, which cached your routes and config
- `laravel/socialite` is suggested (only needed for the optional social sign up routes)
- Added a Pest test suite and CI for PHP 8.3 / 8.4 on Laravel 12 / 13
- The Filament v3 line continues on the `v3` branch
