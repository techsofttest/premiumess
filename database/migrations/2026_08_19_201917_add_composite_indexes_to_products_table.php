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
        Schema::table('products', function (Blueprint $table) {
            try { $table->index(['is_active', 'category_id'], 'idx_products_active_cat'); } catch (\Throwable $e) {}
            try { $table->index(['is_active', 'brand_id'], 'idx_products_active_brd'); } catch (\Throwable $e) {}
            try { $table->index(['is_active', 'is_featured'], 'idx_products_active_feat'); } catch (\Throwable $e) {}
        });

        Schema::table('orders', function (Blueprint $table) {
            try { $table->index(['customer_id', 'status'], 'idx_orders_cust_stat'); } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_active_category');
            $table->dropIndex('idx_products_active_brand');
            $table->dropIndex('idx_products_active_featured');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_customer_status');
        });
    }
};
