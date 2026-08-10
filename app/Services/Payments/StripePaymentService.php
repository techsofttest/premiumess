<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\StripeWebhookLog;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripePaymentService implements PaymentGatewayInterface
{
    protected StripeClient $stripe;
    protected string $currency;

    public function __construct()
    {
        $secret = config('services.stripe.secret');
      
        $this->stripe = new StripeClient($secret);
        $this->currency = config('services.stripe.currency', 'AED') ?: 'AED';
    }

    public function createPaymentIntent(Order $order): array
    {
        $amount = (int) round($order->grand_total * 100);

        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => strtolower($this->currency),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error: ' . $e->getMessage());
            throw new \Exception('Stripe API Configuration Error: ' . $e->getMessage() . '. Please verify STRIPE_SECRET in your .env file.');
        }

        // Create transaction record
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_type' => 'payment_intent_created',
            'payment_intent' => $paymentIntent->id,
            'status' => 'pending',
            'amount' => $order->grand_total,
            'currency' => $this->currency,
            'response' => $paymentIntent->toArray(),
        ]);

        // Save Stripe PI info on the Order itself
        $order->update([
            'payment_amount' => $order->grand_total,
            'payment_currency' => $this->currency,
            'stripe_payment_intent' => $paymentIntent->id,
            'payment_status' => 'pending',
        ]);

        return [
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $order->grand_total,
            'currency' => $this->currency,
        ];
    }

    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): array
    {
        $lineItems = [];

        foreach ($order->items as $item) {
            $unitAmount = (int) round($item->price * 100);
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($this->currency),
                    'product_data' => [
                        'name' => $item->product_name,
                        'description' => $item->variant_details ? "Size/Variant: {$item->variant_details}" : 'Luxury Fragrance',
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => $item->quantity,
            ];
        }

        if ((float) $order->shipping_cost > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($this->currency),
                    'product_data' => [
                        'name' => 'Shipping & Express Courier Delivery',
                    ],
                    'unit_amount' => (int) round($order->shipping_cost * 100),
                ],
                'quantity' => 1,
            ];
        }

        $sessionPayload = [
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl . (str_contains($successUrl, '?') ? '&' : '?') . 'order_number=' . urlencode($order->order_number) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl . (str_contains($cancelUrl, '?') ? '&' : '?') . 'cancelled=1',
            'client_reference_id' => $order->order_number,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ];

        if ($customerEmail = ($order->customer_email ?: $order->email)) {
            $sessionPayload['customer_email'] = $customerEmail;
        }

        try {
            $session = $this->stripe->checkout->sessions->create($sessionPayload);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe Checkout Session API Error: ' . $e->getMessage());
            throw new \Exception('Stripe Hosted Checkout Error: ' . $e->getMessage() . '. Please verify STRIPE_SECRET in your .env file.');
        }

        // Create transaction record
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_type' => 'checkout_session_created',
            'payment_intent' => $session->id,
            'status' => 'pending',
            'amount' => $order->grand_total,
            'currency' => $this->currency,
            'response' => $session->toArray(),
        ]);

        $order->update([
            'payment_amount' => $order->grand_total,
            'payment_currency' => $this->currency,
            'stripe_payment_intent' => $session->id,
            'payment_status' => 'pending',
        ]);

        return [
            'checkout_url' => $session->url,
            'session_id' => $session->id,
            'client_secret' => $session->id,
            'amount' => $order->grand_total,
            'currency' => $this->currency,
        ];
    }

    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
        return $paymentIntent->toArray();
    }

    public function handleWebhook(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.stripe.webhook_secret');
        $tolerance = config('services.stripe.webhook_tolerance', 300);

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret, $tolerance);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            throw $e;
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook invalid payload: ' . $e->getMessage());
            throw $e;
        }

        $eventId = $event->id;
        $eventType = $event->type;

        // Idempotency check: check if event has already been processed
        $existingLog = StripeWebhookLog::where('event_id', $eventId)->first();
        if ($existingLog && $existingLog->processed) {
            Log::info("Stripe Webhook Event {$eventId} already processed.");
            return true;
        }

        $webhookLog = StripeWebhookLog::updateOrCreate(
            ['event_id' => $eventId],
            [
                'provider' => 'stripe',
                'event_type' => $eventType,
                'payload' => $event->toArray(),
                'processed' => false,
            ]
        );

        try {
            DB::transaction(function () use ($event, $eventType, $eventId, $webhookLog) {
                switch ($eventType) {
                    case 'checkout.session.completed':
                        $this->handleCheckoutSessionCompleted($event->data->object, $eventId);
                        break;
                    case 'payment_intent.succeeded':
                        $this->handlePaymentIntentSucceeded($event->data->object, $eventId);
                        break;
                    case 'payment_intent.processing':
                        $this->handlePaymentIntentProcessing($event->data->object, $eventId);
                        break;
                    case 'payment_intent.payment_failed':
                        $this->handlePaymentIntentFailed($event->data->object, $eventId);
                        break;
                    case 'payment_intent.canceled':
                        $this->handlePaymentIntentCanceled($event->data->object, $eventId);
                        break;
                    case 'charge.refunded':
                        $this->handleChargeRefunded($event->data->object, $eventId);
                        break;
                    default:
                        Log::info("Unhandled Stripe webhook event: {$eventType}");
                        break;
                }

                $webhookLog->update([
                    'processed' => true,
                    'error' => null,
                ]);
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Error processing Stripe Webhook Event {$eventId}: " . $e->getMessage());
            $webhookLog->update([
                'processed' => false,
                'error' => $e->getMessage() . "\n" . $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function handlePaymentIntentSucceeded($paymentIntent, string $eventId): void
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;
        $order = null;

        if ($orderId) {
            $order = Order::with('items')->find($orderId);
        }

        if (!$order) {
            // Find by payment intent ID fallback
            $order = Order::with('items')->where('stripe_payment_intent', $paymentIntent->id)->first();
        }

        if (!$order) {
            throw new \Exception("Order not found for Stripe PaymentIntent {$paymentIntent->id}");
        }

        // Check if order is already marked as paid
        if ($order->payment_status === 'paid') {
            Log::info("Order {$order->order_number} is already paid.");
            return;
        }

        $chargeId = $paymentIntent->latest_charge ?? null;

        // Create transaction record
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_type' => 'payment_intent.succeeded',
            'payment_intent' => $paymentIntent->id,
            'charge_id' => $chargeId,
            'event_id' => $eventId,
            'status' => 'succeeded',
            'amount' => $paymentIntent->amount / 100,
            'currency' => strtoupper($paymentIntent->currency),
            'response' => (array) $paymentIntent,
        ]);

        // Reduce inventory inside the transaction (Phase 10: exclusive to webhook processing)
        $this->decrementVariantStock($order);

        // Update Order fields
        $order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed', // update order status to confirmed
            'stripe_charge_id' => $chargeId,
            'paid_at' => now(),
            'payment_metadata' => array_merge((array) $order->payment_metadata, [
                'charge_id' => $chargeId,
                'payment_intent_succeeded_event_id' => $eventId,
            ]),
        ]);

        if ($order->coupon_code) {
            $coupon = \App\Models\Coupon::where('coupon_code', $order->coupon_code)->first();
            if ($coupon) {
                $alreadyLogged = \App\Models\CouponUsage::where('order_id', $order->id)->exists();
                if (!$alreadyLogged) {
                    \App\Models\CouponUsage::create([
                        'coupon_id' => $coupon->id,
                        'customer_id' => $order->customer_id,
                        'order_id' => $order->id,
                        'discount_amount' => $order->discount,
                    ]);
                }
            }
        }

        Log::info("Order {$order->order_number} successfully paid via Stripe.");
    }

    protected function handlePaymentIntentProcessing($paymentIntent, string $eventId): void
    {
        $order = Order::where('stripe_payment_intent', $paymentIntent->id)->first();
        if (!$order) return;

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_type' => 'payment_intent.processing',
            'payment_intent' => $paymentIntent->id,
            'event_id' => $eventId,
            'status' => 'processing',
            'amount' => $paymentIntent->amount / 100,
            'currency' => strtoupper($paymentIntent->currency),
            'response' => (array) $paymentIntent,
        ]);

        $order->update([
            'payment_status' => 'processing',
        ]);
    }

    protected function handlePaymentIntentFailed($paymentIntent, string $eventId): void
    {
        $order = Order::where('stripe_payment_intent', $paymentIntent->id)->first();
        if (!$order) return;

        $failureMessage = $paymentIntent->last_payment_error?->message ?? 'Payment failed';

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_type' => 'payment_intent.payment_failed',
            'payment_intent' => $paymentIntent->id,
            'event_id' => $eventId,
            'status' => 'failed',
            'amount' => $paymentIntent->amount / 100,
            'currency' => strtoupper($paymentIntent->currency),
            'response' => (array) $paymentIntent,
        ]);

        $order->update([
            'payment_status' => 'failed',
            'payment_failure_reason' => $failureMessage,
        ]);
    }

    protected function handlePaymentIntentCanceled($paymentIntent, string $eventId): void
    {
        $order = Order::where('stripe_payment_intent', $paymentIntent->id)->first();
        if (!$order) return;

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_type' => 'payment_intent.canceled',
            'payment_intent' => $paymentIntent->id,
            'event_id' => $eventId,
            'status' => 'canceled',
            'amount' => $paymentIntent->amount / 100,
            'currency' => strtoupper($paymentIntent->currency),
            'response' => (array) $paymentIntent,
        ]);

        $order->update([
            'payment_status' => 'cancelled',
        ]);
    }

    protected function handleChargeRefunded($charge, string $eventId): void
    {
        $order = Order::where('stripe_charge_id', $charge->id)->first();
        if (!$order) {
            $order = Order::where('stripe_payment_intent', $charge->payment_intent)->first();
        }
        if (!$order) return;

        $refundedAmount = $charge->amount_refunded / 100;
        $isFullRefund = $charge->refunded;

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_type' => 'charge.refunded',
            'payment_intent' => $charge->payment_intent,
            'charge_id' => $charge->id,
            'event_id' => $eventId,
            'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
            'amount' => $refundedAmount,
            'currency' => strtoupper($charge->currency),
            'response' => (array) $charge,
        ]);

        $order->update([
            'payment_status' => $isFullRefund ? 'refunded' : 'partially_refunded',
            'status' => $isFullRefund ? 'refunded' : $order->status,
        ]);
    }

    protected function handleCheckoutSessionCompleted($session, string $eventId): void
    {
        $orderId = $session->metadata->order_id ?? null;
        $orderNumber = $session->metadata->order_number ?? $session->client_reference_id ?? null;

        $order = null;
        if ($orderId) {
            $order = Order::with('items')->find($orderId);
        }
        if (!$order && $orderNumber) {
            $order = Order::with('items')->where('order_number', $orderNumber)->first();
        }

        if (!$order) {
            Log::warning("Order not found for Stripe Checkout Session {$session->id}");
            return;
        }

        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'paid_at' => now(),
            'stripe_payment_intent' => $session->payment_intent ?: $session->id,
        ]);

        $this->decrementVariantStock($order);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_type' => 'checkout.session.completed',
            'payment_intent' => $session->payment_intent ?: $session->id,
            'charge_id' => $session->id,
            'status' => 'succeeded',
            'amount' => $order->grand_total,
            'currency' => $this->currency,
            'response' => (array) $session,
        ]);
    }

    private function decrementVariantStock(Order $order): void
    {
        try {
            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    $variant = ProductVariant::find($item->variant_id);
                    if ($variant) {
                        $oldStock = $variant->stock;
                        $variant->decrement('stock', $item->quantity);
                        Log::info("Stock updated for variant {$item->variant_id}: reduced from {$oldStock} to {$variant->fresh()->stock} by quantity {$item->quantity}");
                    } else {
                        Log::warning("Variant {$item->variant_id} not found for order item {$item->id}");
                    }
                } else {
                    Log::warning("Order item {$item->id} has no variant_id");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error reducing inventory for order {$order->id}: " . $e->getMessage());
        }
    }
}
