<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShopFactory extends Factory
{
  public function definition(): array
  {
    return [
      'id' => Str::uuid(),
      'user_id' => User::factory(),
      'name' => fake()->company(),
      'shop_description' => fake()->sentence(),
      'shop_logo' => null,
    ];
  }
}
