<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('philosophies', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->enum('type', ['about', 'mission', 'vision']);
            $table->softDeletes();
            $table->timestamps();
        });

        $now = Carbon::now();

        DB::table('philosophies')->insert([
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
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('philosophies');
    }
};
