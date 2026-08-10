<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('product_row');
            $table->string('source')->default('latest_products');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('link_label')->nullable();
            $table->string('link_url')->nullable();
            $table->string('background_color')->nullable();
            $table->unsignedSmallInteger('item_limit')->default(12);
            $table->json('product_ids')->nullable();
            $table->json('category_ids')->nullable();
            $table->json('brand_ids')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_page_sections');
    }
};
