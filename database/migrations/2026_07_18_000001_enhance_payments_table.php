<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to existing payments table
        Schema::table('payments', function (Blueprint $table) {
            // Add user_id foreign key (nullable — guest checkout)
            $table->foreignId('user_id')->nullable()->after('course_id')->constrained()->nullOnDelete();

            // Rename customer_* columns to buyer_*
            $table->renameColumn('customer_name', 'buyer_name');
            $table->renameColumn('customer_email', 'buyer_email');
            $table->renameColumn('customer_phone', 'buyer_phone');

            // Add gateway column
            $table->string('gateway', 50)->default('easebuzz')->after('currency');

            // Rename payment_response to gateway_response
            $table->renameColumn('payment_response', 'gateway_response');

            // Add payment_id (Easebuzz's reference)
            $table->string('payment_id', 100)->nullable()->after('gateway');
        });

        // Modify status enum to include cancelled and refunded
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->renameColumn('buyer_name', 'customer_name');
            $table->renameColumn('buyer_email', 'customer_email');
            $table->renameColumn('buyer_phone', 'customer_phone');
            $table->dropColumn('gateway');
            $table->dropColumn('payment_id');
            $table->renameColumn('gateway_response', 'payment_response');
            $table->string('status', 20)->default('pending')->change();
        });
    }
};