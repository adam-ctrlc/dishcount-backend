<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if table is empty
        if (Category::count() > 0) {
            return;
        }

        $now = Carbon::now();

        $categories = [
            ['name' => 'Pizza', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Burgers', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pasta', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Salads', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Desserts', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Drinks', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Breakfast', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Lunch', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dinner', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Filipino', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Chinese', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Japanese', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Korean', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Thai', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Vietnamese', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Indian', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Malaysian', 'created_at' => $now, 'updated_at' => $now],
        ];

        Category::insert($categories);
    }
}