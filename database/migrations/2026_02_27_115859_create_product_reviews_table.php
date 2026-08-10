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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('review_product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('review_name');
            $table->string('review_email');

            $table->integer('review_rating'); // 1 to 5

            $table->string('review_title')->nullable();
            $table->longText('review_content');

            $table->boolean('review_status')->default(false); // false = hidden

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
