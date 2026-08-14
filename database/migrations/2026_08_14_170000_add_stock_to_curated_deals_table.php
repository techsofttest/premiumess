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
        Schema::table('curated_deals', function (Blueprint $table) {
            if (!Schema::hasColumn('curated_deals', 'stock')) {
                $table->integer('stock')->default(100)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curated_deals', function (Blueprint $table) {
            if (Schema::hasColumn('curated_deals', 'stock')) {
                $table->dropColumn('stock');
            }
        });
    }
};
