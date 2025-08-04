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
      'rating' => fake()->numberBetween(1, 5),
    ];
  }
}
