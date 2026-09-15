<?php

namespace TomatoPHP\FilamentTenancy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use TomatoPHP\FilamentTenancy\Models\Tenant;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'id' => Str::of($name)->slug('_')->limit(40, '')->toString(),
            'name' => $name,
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'password' => Hash::make('password'),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Tenant $tenant): void {
            if (! $tenant->domains()->exists()) {
                $tenant->domains()->create(['domain' => Str::of($tenant->getTenantKey())->slug()->toString()]);
            }
        });
    }
}
