<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        $types = ['login_success', 'purchase_created', 'purchase_updated', 'order_shipped', 'order_delivered', 'payment_received'];
        $type = fake()->randomElement($types);
        
        $data = [];
        switch ($type) {
            case 'login_success':
                $data = [
                    'login_time' => now()->toISOString(),
                    'user_name' => fake()->name()
                ];
                break;
            case 'purchase_created':
                $data = [
                    'purchase_id' => fake()->uuid(),
                    'total_amount' => fake()->randomFloat(2, 10, 1000),
                    'product_name' => fake()->words(3, true)
                ];
                break;
            case 'purchase_updated':
                $data = [
                    'purchase_id' => fake()->uuid(),
                    'new_status' => fake()->randomElement(['To Ship', 'To Receive', 'Completed'])
                ];
                break;
            default:
                $data = [
                    'message' => fake()->sentence(),
                    'timestamp' => now()->toISOString()
                ];
        }

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'data' => $data,
            'read_at' => fake()->optional(0.3)->dateTimeThisMonth(), // 30% chance of being read
        ];
    }

    public function unread()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => null,
            ];
        });
    }

    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => fake()->dateTimeThisMonth(),
            ];
        });
    }
}