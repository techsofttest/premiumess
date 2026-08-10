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
        Schema::create('delivery_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('delivery_date');
            $table->unsignedBigInteger('delivery_slot_id');
            $table->string('status')->default('draft'); // draft, preparing, ready, in_progress, completed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('delivery_slot_id')->references('id')->on('time_slots')->onDelete('cascade');
        });

        Schema::create('delivery_session_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_session_id');
            $table->unsignedBigInteger('order_id');
            $table->integer('stop_sequence')->default(0);
            $table->string('eta')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('status')->default('pending'); // pending, delivered, failed
            $table->string('failure_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('delivery_session_id')->references('id')->on('delivery_sessions')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        Schema::create('delivery_compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('delivery_session_order_id');
            $table->string('thermometer_photo')->nullable();
            $table->decimal('temperature_reading', 5, 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('captured_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('delivery_session_order_id')->references('id')->on('delivery_session_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_compliance_logs');
        Schema::dropIfExists('delivery_session_orders');
        Schema::dropIfExists('delivery_sessions');
    }
};
