<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('quiz_attempts', 'selected_answer')) {
                $table->char('selected_answer', 1)->after('user_id');
            }
            if (!Schema::hasColumn('quiz_attempts', 'is_correct')) {
                $table->boolean('is_correct')->after('selected_answer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['selected_answer', 'is_correct']);
        });
    }
};