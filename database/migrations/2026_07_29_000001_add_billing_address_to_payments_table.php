<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('billing_address_line1')->nullable()->after('buyer_phone');
            $table->string('billing_address_line2')->nullable()->after('billing_address_line1');
            $table->string('billing_city', 120)->nullable()->after('billing_address_line2');
            $table->string('billing_state', 120)->nullable()->after('billing_city');
            $table->string('billing_postal_code', 20)->nullable()->after('billing_state');
            $table->char('billing_country', 2)->nullable()->after('billing_postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'billing_address_line1',
                'billing_address_line2',
                'billing_city',
                'billing_state',
                'billing_postal_code',
                'billing_country',
            ]);
        });
    }
};
