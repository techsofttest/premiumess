<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'customer_id', 'customer_name', 'customer_email', 'customer_phone',
        'first_name', 'last_name', 'email', 'phone',
        'country', 'address', 'apartment', 'city', 'state', 'pin_code', 'billing_details',
        'shipping_method', 'payment_method', 'payment_status', 'payment_reference', 'status',
        'subtotal', 'shipping_cost', 'discount', 'coupon_code', 'grand_total', 'notes',
        'shipping_name', 'shipping_phone', 'shipping_address_line_1', 'shipping_address_line_2',
        'shipping_suburb', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country',
        'shipping_latitude', 'shipping_longitude', 'shipping_google_place_id', 'delivery_type', 'warehouse_id',
        'delivery_slot_id', 'delivery_date', 'delivery_notes', 'delivery_distance_km',
        'payment_amount', 'payment_currency', 'stripe_payment_intent', 'stripe_charge_id', 'paid_at', 'payment_failure_reason', 'payment_metadata',
        'assigned_staff_id', 'assigned_at', 'assigned_by'
    ];
    
    protected $casts = [
        'billing_details' => 'array',
        'payment_metadata' => 'array',
        'paid_at' => 'datetime',
        'payment_status' => PaymentStatus::class,
        'status' => OrderStatus::class,
        'assigned_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function deliverySessionOrders()
    {
        return $this->hasMany(DeliverySessionOrder::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function reduceInventoryStock(): void
    {
        $metadata = (array) ($this->payment_metadata ?? []);
        if (!empty($metadata['stock_reduced_at'])) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->loadMissing(['items.product.variants']);

            foreach ($this->items as $item) {
                // 1. Check if Curated Deal
                $deal = $item->getCuratedDeal();

                if ($deal) {
                    $lockedDeal = \App\Models\CuratedDeal::where('id', $deal->id)->lockForUpdate()->first();
                    if ($lockedDeal) {
                        $oldStock = (int) ($lockedDeal->stock ?? 0);
                        $newStock = max(0, $oldStock - (int) $item->quantity);
                        $lockedDeal->update(['stock' => $newStock]);
                        \Illuminate\Support\Facades\Log::info("Curated Deal stock reduced for '{$lockedDeal->name}' (ID {$lockedDeal->id}): from {$oldStock} to {$newStock} (qty: {$item->quantity})");
                    }
                    continue;
                }

                // 2. Standard Product Variant stock reduction
                $variantId = $item->variant_id;
                if (!$variantId && $item->product_id) {
                    $product = Product::with('variants')->find($item->product_id);
                    if ($product) {
                        $v = $product->variants->first(fn($v) => (int) $v->stock > 0) ?? $product->variants->first();
                        $variantId = $v?->id;
                    }
                }

                if ($variantId) {
                    $lockedVariant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();
                    if ($lockedVariant) {
                        $oldStock = (int) $lockedVariant->stock;
                        $newStock = max(0, $oldStock - (int) $item->quantity);
                        $lockedVariant->update(['stock' => $newStock]);
                        \Illuminate\Support\Facades\Log::info("Variant stock reduced for variant ID {$lockedVariant->id}: from {$oldStock} to {$newStock} (qty: {$item->quantity})");
                    }
                }
            }

            $metadata = (array) ($this->payment_metadata ?? []);
            $metadata['stock_reduced_at'] = now()->toIso8601String();
            $this->update(['payment_metadata' => $metadata]);
        });
    }

    public function sendPaymentConfirmationEmails(): void
    {
        try {
            $metadata = (array) ($this->payment_metadata ?? []);
            if (!empty($metadata['email_sent_at'])) {
                return;
            }

            $order = $this->fresh(['items.product.brand', 'customer']);
            if (!$order) return;

            $recipientEmail = $order->customer_email ?: $order->email ?: $order->customer?->email;
            if ($recipientEmail) {
                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\OrderInvoiceMail($order));
            }

            $adminEmail = env('ADMIN_NOTIFICATION_EMAIL', 'sales@premium-perfumes.com');
            if ($adminEmail) {
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\AdminOrderNotificationMail($order));
            }

            $metadata['email_sent_at'] = now()->toIso8601String();
            $order->update(['payment_metadata' => $metadata]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed sending payment confirmation emails for Order #{$this->order_number}: " . $e->getMessage());
        }
    }
}

