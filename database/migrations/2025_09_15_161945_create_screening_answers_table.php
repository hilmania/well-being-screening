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
        Schema::create('screening_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_id')->constrained('well_being_screenings')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('screening_questions')->onDelete('cascade');
            $table->text('answer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_answers');
    }
};
