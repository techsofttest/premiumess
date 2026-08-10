<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            }

            foreach ([
                'customer_name' => fn (Blueprint $table) => $table->string('customer_name')->nullable()->after('customer_id'),
                'customer_email' => fn (Blueprint $table) => $table->string('customer_email')->nullable()->after('customer_name'),
                'customer_phone' => fn (Blueprint $table) => $table->string('customer_phone')->nullable()->after('customer_email'),
                'shipping_suburb' => fn (Blueprint $table) => $table->string('shipping_suburb')->nullable()->after('shipping_address_line_2'),
                'delivery_type' => fn (Blueprint $table) => $table->string('delivery_type')->default('courier')->after('shipping_longitude'),
                'warehouse_id' => fn (Blueprint $table) => $table->unsignedBigInteger('warehouse_id')->nullable()->after('delivery_type'),
                'delivery_slot_id' => fn (Blueprint $table) => $table->unsignedBigInteger('delivery_slot_id')->nullable()->after('warehouse_id'),
                'delivery_notes' => fn (Blueprint $table) => $table->text('delivery_notes')->nullable()->after('delivery_slot_id'),
                'delivery_distance_km' => fn (Blueprint $table) => $table->decimal('delivery_distance_km', 8, 2)->nullable()->after('delivery_notes'),
            ] as $column => $adder) {
                if (!Schema::hasColumn('orders', $column)) {
                    $adder($table);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'customer_id')) {
                $table->dropForeign(['customer_id']);
            }

            $dropColumns = [];
            foreach ([
                'delivery_distance_km',
                'delivery_notes',
                'delivery_slot_id',
                'warehouse_id',
                'delivery_type',
                'shipping_suburb',
                'customer_phone',
                'customer_email',
                'customer_name',
                'customer_id',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
