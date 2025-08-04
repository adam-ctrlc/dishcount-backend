<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('categories')->insert([
            ['name' => 'Pizza'],
            ['name' => 'Burgers'],
            ['name' => 'Pasta'],
            ['name' => 'Salads'],
            ['name' => 'Desserts'],
            ['name' => 'Drinks'],
            ['name' => 'Breakfast'],
            ['name' => 'Lunch'],
            ['name' => 'Dinner'],
            ['name' => 'Filipino'],
            ['name' => 'Chinese'],
            ['name' => 'Japanese'],
            ['name' => 'Korean'],
            ['name' => 'Thai'],
            ['name' => 'Vietnamese'],
            ['name' => 'Indian'],
            ['name' => 'Malaysian'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
