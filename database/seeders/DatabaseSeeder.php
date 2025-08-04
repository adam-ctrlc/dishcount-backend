<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles if not present
        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                ['name' => 'admin'],
                ['name' => 'seller'],
                ['name' => 'customer'],
            ]);
        }

        // Seed categories first
        $categories = [
            ['name' => 'Food'],
            ['name' => 'Beverages'],
            ['name' => 'Snacks'],
            ['name' => 'Desserts'],
            ['name' => 'Main Course'],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }

        // Seed payment methods
        $this->call(PaymentMethodSeeder::class);

        // Seed payment statuses if not present
        if (DB::table('payment_statuses')->count() === 0) {
            PaymentStatus::create(['status' => 'Pending']);
            PaymentStatus::create(['status' => 'Completed']);
            PaymentStatus::create(['status' => 'Refunded & Returned']);
            PaymentStatus::create(['status' => 'Cancelled']);
        }

        // Create a hardcoded user for testing
        $user1 = User::create([
            'id' => Str::uuid(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => null,
            'username' => 'johndoe',
            'email' => 'johndoe@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'has_shop' => true,
            'birth_date' => null,
            'address' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'postal_code' => '',
            'profile_picture' => null,
            'phone' => '',
            'is_active' => true,
        ]);

        $user2 = User::create([
            'id' => Str::uuid(),
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'middle_name' => null,
            'username' => 'janesmith',
            'email' => 'janesmith@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'has_shop' => true,
            'birth_date' => null,
            'address' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'postal_code' => '',
            'profile_picture' => null,
            'phone' => '',
            'is_active' => true,
        ]);

        // Create a shop for the hardcoded user
        $shop = Shop::create([
            'id' => Str::uuid(),
            'user_id' => $user1->id,
            'name' => 'John\'s Shop',
            'shop_description' => 'A shop owned by John Doe',
            'shop_logo' => null,
        ]);

        $shop2 = Shop::create([
            'id' => Str::uuid(),
            'user_id' => $user2->id,
            'name' => 'Jane\'s Shop',
            'shop_description' => 'A shop owned by Jane Smith',
            'shop_logo' => null,
        ]);

        // Create some products for the hardcoded user's shop
        for ($i = 1; $i <= 5; $i++) {
            Product::factory()
                ->forShop($shop)
                ->create([
                    'product_name' => "John's Product $i",
                    'product_description' => "Description for John's Product $i",
                ]);
        }

        // Create users with shops and their products
        User::factory()
            ->count(10)
            ->create(['role_id' => 2, 'has_shop' => true])
            ->each(function ($user) {
                $shop = Shop::factory()->create(['user_id' => $user->id]);
                Product::factory()
                    ->count(5)
                    ->forShop($shop)
                    ->create();
            });

        // Create regular users without shops
        User::factory()
            ->count(20)
            ->create(['role_id' => 3, 'has_shop' => false]); // customer role

        // Create ratings
        Rating::factory()
            ->count(100)
            ->create();

        // Create purchases
        Purchase::factory()
            ->count(50)
            ->create();
    }
}
