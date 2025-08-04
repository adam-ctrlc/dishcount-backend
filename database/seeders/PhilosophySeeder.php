<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Philosophy;
use Carbon\Carbon;

class PhilosophySeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if table is empty
        if (Philosophy::count() > 0) {
            return;
        }

        $now = Carbon::now();

        $philosophies = [
            [
                'description' => 'To create extraordinary culinary experiences that bring people together, celebrating the art of fine dining through innovative cuisine, exceptional service, and warm hospitality that makes every guest feel valued and inspired.',
                'type' => 'mission',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'description' => 'To be the premier destination for culinary excellence, setting new standards in the restaurant industry through sustainable practices, innovative flavors, and creating lasting memories that inspire our guests to return time and again.',
                'type' => 'vision',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'description' => 'We are dedicated to providing exceptional dining experiences through innovative cuisine, outstanding service, and a commitment to sustainability. Our team believes in using fresh, locally-sourced ingredients to create memorable meals that bring people together.',
                'type' => 'about',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        Philosophy::insert($philosophies);
    }
}