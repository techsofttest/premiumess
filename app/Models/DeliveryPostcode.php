<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryPostcode extends Model
{
    protected $fillable = [
        'postcode',
        'warehouse_id',
        'delivery_fee',
        'free_shipping_threshold',
        'is_active',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
