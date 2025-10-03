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
        Schema::table('screening_questions', function (Blueprint $table) {
            $table->enum('question_type', ['likert', 'text'])->default('likert')->after('question_text');
            $table->text('placeholder')->nullable()->after('question_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('screening_questions', function (Blueprint $table) {
            $table->dropColumn(['question_type', 'placeholder']);
        });
    }
};
