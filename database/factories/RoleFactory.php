<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['admin', 'seller', 'customer']),
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'admin',
            ];
        });
    }

    public function seller()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'seller',
            ];
        });
    }

    public function customer()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'customer',
            ];
        });
    }
}