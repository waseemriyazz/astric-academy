<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
            $table->string('duration')->nullable()->after('category');
            $table->json('features')->nullable()->after('duration');
            $table->string('icon_name')->nullable()->after('features');
            $table->decimal('price_min', 10, 2)->default(0)->after('icon_name');
            $table->decimal('price_max', 10, 2)->default(0)->after('price_min');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['category', 'duration', 'features', 'icon_name', 'price_min', 'price_max']);
        });
    }
};