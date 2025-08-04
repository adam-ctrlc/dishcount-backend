<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
  public function definition(): array
  {
    return [
      'shop_id' => Shop::factory(),
      'category_id' => Category::inRandomOrder()->first()->id ?? Category::factory(),
      'product_name' => fake()->words(3, true),
      'product_description' => fake()->paragraph(),
      'price' => fake()->randomFloat(2, 10, 1000),
      'image' => null,
      'stock' => fake()->numberBetween(0, 100),
      'discount' => fake()->randomFloat(1, 0, 9.9),
      'is_active' => true,
    ];
  }

  public function forShop(Shop $shop)
  {
    return $this->state(function (array $attributes) use ($shop) {
      return [
        'shop_id' => $shop->id,
      ];
    });
  }
}
