<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        $methods = [
            ['name' => 'Maya', 'number' => '09123456789'],
            ['name' => 'GCash', 'number' => '09987654321'],
            ['name' => 'Cash on Delivery', 'number' => '000000000000'],
            ['name' => 'PayPal', 'number' => 'paypal@example.com'],
            ['name' => 'Credit Card', 'number' => '1234567890123456'],
        ];

        $method = fake()->randomElement($methods);

        return [
            'name' => $method['name'],
            'number' => $method['number'],
            'image' => null,
        ];
    }
}