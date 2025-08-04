<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if table is empty
        if (PaymentMethod::count() > 0) {
            return;
        }

        $paymentMethods = [
            [
                'name' => 'Maya',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/Maya_logo.svg/512px-Maya_logo.svg.png',
                'number' => '000000000001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cash on Delivery',
                'image' => null,
                'number' => '000000000000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GCash',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/GCash_logo.svg/512px-GCash_logo.svg.png',
                'number' => '000000000002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PayPal',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/512px-PayPal.svg.png',
                'number' => '000000000003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Credit Card',
                'image' => null,
                'number' => '000000000004',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        PaymentMethod::insert($paymentMethods);
    }
}