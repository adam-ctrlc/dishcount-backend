<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'rating' => fake()->randomFloat(2, 1.00, 5.00), // Decimal with 2 places, between 1.00 and 5.00
        ];
    }

    public function highRating()
    {
        return $this->state(function (array $attributes) {
            return [
                'rating' => fake()->randomFloat(2, 4.00, 5.00),
            ];
        });
    }

    public function lowRating()
    {
        return $this->state(function (array $attributes) {
            return [
                'rating' => fake()->randomFloat(2, 1.00, 2.50),
            ];
        });
    }
}
