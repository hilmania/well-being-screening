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
            $table->string('group_name')->default('default')->after('placeholder');
            $table->boolean('is_active')->default(true)->after('group_name');
            $table->integer('order')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('screening_questions', function (Blueprint $table) {
            $table->dropColumn(['group_name', 'is_active', 'order']);
        });
    }
};
