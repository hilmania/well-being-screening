<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('volunteers_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_id')->constrained('well_being_screenings')->onDelete('cascade');
            $table->foreignId('volunteer_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteers_responses');
    }
};
