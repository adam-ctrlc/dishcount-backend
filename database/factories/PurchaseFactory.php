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
            'payment_status_id' => PaymentStatus::inRandomOrder()->first()->id ?? 1,
            'payment_method_id' => PaymentMethod::inRandomOrder()->first()->id ?? 1,
            'user_id' => User::factory(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'total_price' => $total_price,
            'reference_number' => Str::random(12),
            'receipt' => fake()->optional()->filePath(),
            'is_active' => fake()->boolean(90), // 90% chance of being active
            'refund_reason' => fake()->optional(0.1)->sentence(), // 10% chance of having refund reason
            'received_at' => fake()->optional(0.7)->dateTimeThisMonth(), // 70% chance of being received
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status_id' => 1, // To Pay
                'received_at' => null,
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status_id' => 4, // Completed
                'received_at' => fake()->dateTimeThisMonth(),
            ];
        });
    }

    public function refunded()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status_id' => 5, // Return/Refund
                'refund_reason' => fake()->sentence(),
            ];
        });
    }
}
