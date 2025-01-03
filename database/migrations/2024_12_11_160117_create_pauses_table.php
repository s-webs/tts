<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_day_id')->constrained()->onDelete('cascade');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('latitude_start', 10, 7)->nullable();
            $table->decimal('longitude_start', 10, 7)->nullable();
            $table->decimal('latitude_end', 10, 7)->nullable();
            $table->decimal('longitude_end', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pauses');
    }
};
