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
            'id' => Str::uuid(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->optional()->firstName(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => 2,
            'has_shop' => fake()->boolean(),
            'birth_date' => fake()->optional()->date(),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->state(),
            'country' => fake()->optional()->country(),
            'postal_code' => fake()->optional()->postcode(),
            'profile_picture' => null,
            'phone' => fake()->optional()->phoneNumber(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
