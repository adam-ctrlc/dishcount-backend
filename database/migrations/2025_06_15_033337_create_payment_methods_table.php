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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('image')->unique()->nullable();
            $table->string('number')->unique();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('payment_methods')->insert([
            ['name' => 'Maya', 'number' => '09123456789'],
            ['name' => 'GCash', 'number' => '09987654321'],
            ['name' => 'Cash on Delivery', 'number' => '000000000000']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
