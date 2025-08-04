<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Purchase;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call all the individual seeders for lookup tables
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            PaymentMethodSeeder::class,
            PaymentStatusSeeder::class,
            PhilosophySeeder::class,
        ]);

        // Create admin user
        $admin = User::create([
            'id' => Str::uuid(),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'middle_name' => null,
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // admin role
            'has_shop' => false,
            'birth_date' => null,
            'address' => '123 Admin Street',
            'city' => 'Admin City',
            'state' => 'Admin State',
            'country' => 'Philippines',
            'postal_code' => '12345',
            'profile_picture' => null,
            'phone' => '+639123456789',
            'is_active' => true,
        ]);

        // Create test seller users
        $user1 = User::create([
            'id' => Str::uuid(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'A',
            'username' => 'johndoe',
            'email' => 'johndoe@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // seller role
            'has_shop' => true,
            'birth_date' => '1990-01-15',
            'address' => '456 Business Ave',
            'city' => 'Manila',
            'state' => 'Metro Manila',
            'country' => 'Philippines',
            'postal_code' => '10001',
            'profile_picture' => null,
            'phone' => '+639111111111',
            'is_active' => true,
        ]);

        $user2 = User::create([
            'id' => Str::uuid(),
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'middle_name' => 'B',
            'username' => 'janesmith',
            'email' => 'janesmith@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // seller role
            'has_shop' => true,
            'birth_date' => '1985-05-20',
            'address' => '789 Commerce St',
            'city' => 'Quezon City',
            'state' => 'Metro Manila',
            'country' => 'Philippines',
            'postal_code' => '11001',
            'profile_picture' => null,
            'phone' => '+639222222222',
            'is_active' => true,
        ]);

        // Create test customer user
        $customer = User::create([
            'id' => Str::uuid(),
            'first_name' => 'Customer',
            'last_name' => 'Test',
            'middle_name' => null,
            'username' => 'customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role_id' => 3, // customer role
            'has_shop' => false,
            'birth_date' => '1992-03-10',
            'address' => '321 Customer Lane',
            'city' => 'Makati',
            'state' => 'Metro Manila',
            'country' => 'Philippines',
            'postal_code' => '12001',
            'profile_picture' => null,
            'phone' => '+639333333333',
            'is_active' => true,
        ]);

        // Create shops for seller users
        $shop1 = Shop::create([
            'id' => Str::uuid(),
            'user_id' => $user1->id,
            'name' => 'John\'s Delicious Kitchen',
            'shop_description' => 'Authentic Filipino cuisine with a modern twist. Fresh ingredients, traditional recipes, and exceptional service.',
            'shop_address' => '456 Business Ave, Manila, Metro Manila',
            'shop_phone' => '+639111111111',
            'shop_email' => 'johns.kitchen@gmail.com',
            'shop_logo' => null,
        ]);

        $shop2 = Shop::create([
            'id' => Str::uuid(),
            'user_id' => $user2->id,
            'name' => 'Jane\'s Bistro & Cafe',
            'shop_description' => 'Cozy cafe serving artisan coffee, fresh pastries, and international fusion dishes.',
            'shop_address' => '789 Commerce St, Quezon City, Metro Manila',
            'shop_phone' => '+639222222222',
            'shop_email' => 'janes.bistro@gmail.com',
            'shop_logo' => null,
        ]);

        // Create featured products for the test shops
        $featuredProducts = [
            ['name' => 'Adobo Rice Bowl', 'description' => 'Classic Filipino adobo served with steamed rice and vegetables', 'price' => 250.00, 'category_id' => 10], // Filipino
            ['name' => 'Beef Burger Deluxe', 'description' => 'Juicy beef patty with cheese, lettuce, tomato, and our special sauce', 'price' => 350.00, 'category_id' => 2], // Burgers
            ['name' => 'Carbonara Pasta', 'description' => 'Creamy pasta with bacon, mushrooms, and parmesan cheese', 'price' => 280.00, 'category_id' => 3], // Pasta
            ['name' => 'Iced Coffee Frappe', 'description' => 'Refreshing iced coffee blend with whipped cream', 'price' => 150.00, 'category_id' => 6], // Drinks
            ['name' => 'Chocolate Cake Slice', 'description' => 'Rich chocolate cake with chocolate ganache', 'price' => 180.00, 'category_id' => 5], // Desserts
        ];

        foreach ($featuredProducts as $index => $product) {
            Product::create([
                'shop_id' => $index < 3 ? $shop1->id : $shop2->id,
                'category_id' => $product['category_id'],
                'product_name' => $product['name'],
                'product_description' => $product['description'],
                'price' => $product['price'],
                'image' => null,
                'stock' => fake()->numberBetween(10, 50),
                'discount' => $index === 0 ? 2.5 : 0.0, // First product has discount
                'is_active' => true,
                'is_featured' => $index < 2, // First two products are featured
            ]);
        }

        // Create additional random users with shops and their products
        User::factory()
            ->count(15)
            ->state(['role_id' => 2, 'has_shop' => true]) // sellers
            ->create()
            ->each(function ($user) {
                $shop = Shop::factory()->create(['user_id' => $user->id]);
                
                // Create mix of regular and featured products
                Product::factory()
                    ->count(8)
                    ->forShop($shop)
                    ->active()
                    ->create();
                    
                // Create a few featured products per shop
                Product::factory()
                    ->count(2)
                    ->forShop($shop)
                    ->featured()
                    ->active()
                    ->create();
            });

        // Create regular customer users
        User::factory()
            ->count(25)
            ->state(['role_id' => 3, 'has_shop' => false]) // customers
            ->create();

        // Create ratings for products
        Rating::factory()
            ->count(150)
            ->create();

        // Create purchases with different statuses
        Purchase::factory()
            ->count(30)
            ->pending()
            ->create();

        Purchase::factory()
            ->count(40)
            ->completed()
            ->create();

        Purchase::factory()
            ->count(5)
            ->refunded()
            ->create();

        // Create notifications for users
        Notification::factory()
            ->count(100)
            ->create();

        // Create some unread notifications
        Notification::factory()
            ->count(50)
            ->unread()
            ->create();

        $this->command->info('Database seeded successfully!');
        $this->command->info('Test accounts created:');
        $this->command->info('Admin: admin@admin.com / password');
        $this->command->info('Seller 1: johndoe@gmail.com / password');
        $this->command->info('Seller 2: janesmith@gmail.com / password');
        $this->command->info('Customer: customer@test.com / password');
    }
}
