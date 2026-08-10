<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySessionOrder extends Model
{
    protected $fillable = [
        'delivery_session_id',
        'order_id',
        'stop_sequence',
        'eta',
        'delivered_at',
        'status',
        'failure_reason',
        'notes',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function deliverySession(): BelongsTo
    {
        return $this->belongsTo(DeliverySession::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function complianceLogs(): HasMany
    {
        return $this->hasMany(DeliveryComplianceLog::class);
    }
}
