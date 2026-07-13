<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Drop columns only if they exist
            $columnsToDrop = [];
            if (Schema::hasColumn('quiz_attempts', 'answers')) {
                $columnsToDrop[] = 'answers';
            }
            if (Schema::hasColumn('quiz_attempts', 'score')) {
                $columnsToDrop[] = 'score';
            }
            if (Schema::hasColumn('quiz_attempts', 'total_points')) {
                $columnsToDrop[] = 'total_points';
            }
            if (Schema::hasColumn('quiz_attempts', 'completed_at')) {
                $columnsToDrop[] = 'completed_at';
            }
            if (Schema::hasColumn('quiz_attempts', 'questions')) {
                $columnsToDrop[] = 'questions';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->text('answers')->nullable();
            $table->integer('score')->nullable();
            $table->integer('total_points')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('questions')->nullable();
        });
    }
};