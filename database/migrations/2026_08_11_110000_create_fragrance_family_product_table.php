<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('fragrance_family_product');

        Schema::create('fragrance_family_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('fragrance_family_id')->constrained('fragrance_families')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'fragrance_family_id'], 'ffp_uniq');
        });

        // Backfill pivot table from existing fragrance_family_id on products table
        $existing = DB::table('products')
            ->whereNotNull('fragrance_family_id')
            ->get(['id', 'fragrance_family_id']);

        foreach ($existing as $row) {
            DB::table('fragrance_family_product')->insertOrIgnore([
                'product_id' => $row->id,
                'fragrance_family_id' => $row->fragrance_family_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fragrance_family_product');
    }
};
