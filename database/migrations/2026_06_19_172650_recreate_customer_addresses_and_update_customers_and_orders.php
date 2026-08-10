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
        // 1. Drop existing customer_addresses table if it exists
        Schema::dropIfExists('customer_addresses');

        // 2. Create customer_addresses table
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->string('label')->nullable();
            $table->string('contact_name');
            $table->string('phone');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('suburb')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postcode');
            $table->string('country')->default('Australia');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('google_place_id')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_default_billing')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Update customers table to add defaults
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('default_shipping_address_id')
                ->nullable()
                ->constrained('customer_addresses')
                ->nullOnDelete();

            $table->foreignId('default_billing_address_id')
                ->nullable()
                ->constrained('customer_addresses')
                ->nullOnDelete();
        });

        // 4. Update orders table to support address snapshot
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_address_line_1')->nullable();
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->string('shipping_country')->nullable();
            $table->decimal('shipping_latitude', 10, 7)->nullable();
            $table->decimal('shipping_longitude', 10, 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_phone',
                'shipping_address_line_1',
                'shipping_address_line_2',
                'shipping_city',
                'shipping_state',
                'shipping_postcode',
                'shipping_country',
                'shipping_latitude',
                'shipping_longitude',
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['default_shipping_address_id']);
            $table->dropForeign(['default_billing_address_id']);
            $table->dropColumn(['default_shipping_address_id', 'default_billing_address_id']);
        });

        Schema::dropIfExists('customer_addresses');
    }
};
