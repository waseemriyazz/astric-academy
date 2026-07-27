<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['payment_id', 'plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plan');
    }
};