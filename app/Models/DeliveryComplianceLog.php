<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryComplianceLog extends Model
{
    protected $fillable = [
        'order_id',
        'delivery_session_order_id',
        'thermometer_photo',
        'temperature_reading',
        'latitude',
        'longitude',
        'captured_at',
        'notes',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'temperature_reading' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function deliverySessionOrder(): BelongsTo
    {
        return $this->belongsTo(DeliverySessionOrder::class);
    }
}
