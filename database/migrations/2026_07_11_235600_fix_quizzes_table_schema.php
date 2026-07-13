<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // Add new columns only if they don't exist
            if (!Schema::hasColumn('quizzes', 'question')) {
                $table->text('question')->after('lesson_id');
            }
            if (!Schema::hasColumn('quizzes', 'option_a')) {
                $table->string('option_a')->after('question');
            }
            if (!Schema::hasColumn('quizzes', 'option_b')) {
                $table->string('option_b')->after('option_a');
            }
            if (!Schema::hasColumn('quizzes', 'option_c')) {
                $table->string('option_c')->after('option_b');
            }
            if (!Schema::hasColumn('quizzes', 'option_d')) {
                $table->string('option_d')->after('option_c');
            }
            if (!Schema::hasColumn('quizzes', 'correct_answer')) {
                $table->char('correct_answer', 1)->after('option_d');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer']);
            
            // Add old columns back
            $table->string('title')->after('lesson_id');
            $table->text('description')->nullable()->after('title');
            
            // Recreate unique index
            $table->unique('lesson_id');
        });
    }
};