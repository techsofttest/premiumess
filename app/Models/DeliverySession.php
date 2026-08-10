<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Services\DeliverySessionService;

class DeliverySession extends Model
{
    protected static function booted(): void
    {
        static::created(function (DeliverySession $session) {
            app(DeliverySessionService::class)->pullAndOptimize($session);
        });
    }
    protected $fillable = [
        'delivery_date',
        'delivery_slot_id',
        'status',
        'started_at',
        'completed_at',
        'staff_id',
        'estimated_distance_km',
        'estimated_duration_minutes',
        'route_generated_at',
        'route_polyline',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'route_generated_at' => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class, 'delivery_slot_id');
    }

    public function sessionOrders(): HasMany
    {
        return $this->hasMany(DeliverySessionOrder::class)->orderBy('stop_sequence');
    }
}
