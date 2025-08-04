<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseFactory extends Factory
{
  public function definition(): array
  {
    $product = Product::factory()->create();
    $quantity = fake()->numberBetween(1, 5);
    $total_price = $product->price * $quantity;

    return [
      'id' => Str::uuid(),
      'payment_status_id' => PaymentStatus::all()->random()->id,
      'payment_method_id' => PaymentMethod::all()->random()->id,
      'user_id' => User::factory(),
      'product_id' => $product->id,
      'quantity' => $quantity,
      'total_price' => $total_price,
      'reference_number' => Str::orderedUuid(),
      'is_active' => true,
    ];
  }
}
