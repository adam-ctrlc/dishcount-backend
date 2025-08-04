<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentStatus;
use Carbon\Carbon;

class PaymentStatusSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if table is empty
        if (PaymentStatus::count() > 0) {
            return;
        }

        $now = Carbon::now();

        $statuses = [
            ['status' => 'To Pay', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'To Ship', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'To Receive', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Completed', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Return/Refund', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Cancelled', 'created_at' => $now, 'updated_at' => $now],
        ];

        PaymentStatus::insert($statuses);
    }
}