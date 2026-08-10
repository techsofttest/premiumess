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
        'shipping_method', 'payment_method', 'payment_status', 'status',
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
}

