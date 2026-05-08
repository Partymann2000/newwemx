<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'status' => 'active',
            'email' => fake()->unique()->safeEmail(),
            'country' => fake()->countryCode(),
            'language' => fake()->randomElement(['en', 'es', 'fr', 'de']),
            'balance' => fake()->randomFloat(2, 0, 1000),
            'status' => fake()->randomElement(['active', 'inactive', 'active', 'active', 'active', 'active', 'active', 'active', 'active', 'active']),
            'email_verified_at' => now(),
            'last_seen_at' => fake()->dateTime(),
            'last_login_at' => fake()->dateTime(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
