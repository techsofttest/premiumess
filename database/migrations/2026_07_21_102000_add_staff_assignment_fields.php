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
         Schema::table('orders', function (Blueprint $table) {
             $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
             $table->timestamp('assigned_at')->nullable();
             $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
         });

         Schema::table('delivery_sessions', function (Blueprint $table) {
             $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
         });
     }

     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::table('orders', function (Blueprint $table) {
             $table->dropForeign(['assigned_staff_id']);
             $table->dropColumn('assigned_staff_id');
             $table->dropColumn('assigned_at');
             $table->dropForeign(['assigned_by']);
             $table->dropColumn('assigned_by');
         });

         Schema::table('delivery_sessions', function (Blueprint $table) {
             $table->dropForeign(['staff_id']);
             $table->dropColumn('staff_id');
         });
     }
};
