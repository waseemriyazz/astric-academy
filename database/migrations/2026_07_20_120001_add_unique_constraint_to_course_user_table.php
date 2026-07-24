<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The table's own creation migration (create_course_user_table) already adds
        // this exact unique constraint via Laravel's default naming convention, so on
        // every real environment it already exists — attempting to add it again fails
        // with "duplicate key name". Check first so this is safe to run regardless of
        // whether that's true for a given database.
        $indexExists = collect(Schema::getIndexes('course_user'))
            ->contains(fn ($index) => $index['name'] === 'course_user_user_id_course_id_unique');

        if ($indexExists) {
            return;
        }

        Schema::table('course_user', function (Blueprint $table) {
            $table->unique(['user_id', 'course_id'], 'course_user_user_id_course_id_unique');
        });
    }

    public function down(): void
    {
        $indexExists = collect(Schema::getIndexes('course_user'))
            ->contains(fn ($index) => $index['name'] === 'course_user_user_id_course_id_unique');

        if (!$indexExists) {
            return;
        }

        Schema::table('course_user', function (Blueprint $table) {
            $table->dropUnique('course_user_user_id_course_id_unique');
        });
    }
};