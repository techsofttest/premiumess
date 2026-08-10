<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_sessions', function (Blueprint $table) {
            $table->text('route_polyline')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_sessions', function (Blueprint $table) {
            $table->dropColumn('route_polyline');
        });
    }
};
