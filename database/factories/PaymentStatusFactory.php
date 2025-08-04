<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentStatusFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['To Pay', 'To Ship', 'To Receive', 'Completed', 'Return/Refund', 'Cancelled'];

        return [
            'status' => fake()->randomElement($statuses),
        ];
    }
}