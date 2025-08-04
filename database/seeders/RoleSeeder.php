<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if table is empty
        if (Role::count() > 0) {
            return;
        }

        $now = Carbon::now();

        $roles = [
            ['name' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'seller', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'customer', 'created_at' => $now, 'updated_at' => $now],
        ];

        Role::insert($roles);
    }
}