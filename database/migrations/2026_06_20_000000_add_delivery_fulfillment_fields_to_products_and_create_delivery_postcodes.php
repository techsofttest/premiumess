<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'requires_direct_delivery')) {
                $table->boolean('requires_direct_delivery')->default(false)->after('is_active');
            }

            if (!Schema::hasColumn('products', 'allows_courier')) {
                $table->boolean('allows_courier')->default(true)->after('requires_direct_delivery');
            }
        });

        Schema::create('delivery_postcodes', function (Blueprint $table) {
            $table->id();
            $table->string('postcode', 20)->index();
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['postcode', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_postcodes');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'allows_courier')) {
                $table->dropColumn('allows_courier');
            }

            if (Schema::hasColumn('products', 'requires_direct_delivery')) {
                $table->dropColumn('requires_direct_delivery');
            }
        });
    }
};
