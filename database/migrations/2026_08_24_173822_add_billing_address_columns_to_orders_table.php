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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('billing_same_as_shipping')->default(true)->after('shipping_country');
            $table->string('billing_name')->nullable()->after('billing_same_as_shipping');
            $table->string('billing_phone')->nullable()->after('billing_name');
            $table->string('billing_address_line_1')->nullable()->after('billing_phone');
            $table->string('billing_address_line_2')->nullable()->after('billing_address_line_1');
            $table->string('billing_city')->nullable()->after('billing_address_line_2');
            $table->string('billing_state')->nullable()->after('billing_city');
            $table->string('billing_postcode')->nullable()->after('billing_state');
            $table->string('billing_country')->nullable()->after('billing_postcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'billing_same_as_shipping',
                'billing_name',
                'billing_phone',
                'billing_address_line_1',
                'billing_address_line_2',
                'billing_city',
                'billing_state',
                'billing_postcode',
                'billing_country',
            ]);
        });
    }
};
