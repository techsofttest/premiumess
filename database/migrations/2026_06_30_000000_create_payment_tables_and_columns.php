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
        // Add payment related columns to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('payment_amount', 10, 2)->default(0.00)->after('grand_total');
            $table->string('payment_currency', 10)->default('AUD')->after('payment_amount');
            $table->string('stripe_payment_intent')->nullable()->index()->after('payment_currency');
            $table->string('stripe_charge_id')->nullable()->after('stripe_payment_intent');
            $table->timestamp('paid_at')->nullable()->after('stripe_charge_id');
            $table->text('payment_failure_reason')->nullable()->after('paid_at');
            $table->json('payment_metadata')->nullable()->after('payment_failure_reason');
        });

        // Create payment transactions table
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('gateway')->default('stripe');
            $table->string('transaction_type'); // e.g. payment, refund, retry
            $table->string('payment_intent')->nullable()->index();
            $table->string('charge_id')->nullable();
            $table->string('event_id')->nullable();
            $table->string('status'); // e.g. pending, succeeded, failed, refunded
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('AUD');
            $table->json('response')->nullable();
            $table->timestamps();
        });

        // Create stripe webhook logs table
        Schema::create('stripe_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('stripe');
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_logs');
        Schema::dropIfExists('payment_transactions');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_amount',
                'payment_currency',
                'stripe_payment_intent',
                'stripe_charge_id',
                'paid_at',
                'payment_failure_reason',
                'payment_metadata',
            ]);
        });
    }
};
