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
        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->timestamps();
        });

        DB::table('payment_statuses')->insert([
            ['status' => 'To Pay'],
            ['status' => 'To Ship'],
            ['status' => 'To Receive'],
            ['status' => 'Completed'],
            ['status' => 'Return/Refund'],
            ['status' => 'Cancelled'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_statuses');
    }
};
