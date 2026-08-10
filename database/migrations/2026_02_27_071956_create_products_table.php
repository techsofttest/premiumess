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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('sku')->nullable()->unique();
            $table->string('name')->index();
            $table->string('slug')->unique();

            $table->string('brand')->nullable();
            $table->string('supplier_code')->nullable();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->integer('stock_in_order')->default(0);
            $table->integer('stock_on_hand')->default(0);

            $table->longText('key_features')->nullable();
            $table->longText('description')->nullable();

            $table->date('expiry_date')->nullable();

            $table->string('featured_image')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->string('meta_title')->nullable();
            $table->longText('meta_description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
