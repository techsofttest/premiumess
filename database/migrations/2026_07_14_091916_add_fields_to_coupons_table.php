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
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('coupon_name')->nullable()->after('coupon_code');
            $table->decimal('minimum_order_amount', 8, 2)->default(0.00)->after('coupon_todate');
            $table->decimal('maximum_discount', 8, 2)->nullable()->after('minimum_order_amount');
            $table->integer('global_usage_limit')->default(0)->after('maximum_discount');
            $table->integer('customer_usage_limit')->default(0)->after('global_usage_limit');
            $table->boolean('first_order_only')->default(false)->after('customer_usage_limit');
            $table->boolean('active')->default(true)->after('first_order_only');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'coupon_name',
                'minimum_order_amount',
                'maximum_discount',
                'global_usage_limit',
                'customer_usage_limit',
                'first_order_only',
                'active'
            ]);
        });
    }
};
