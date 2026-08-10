<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'transaction_type',
        'payment_intent',
        'charge_id',
        'event_id',
        'status',
        'amount',
        'currency',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
        'amount' => 'decimal:2',
        'transaction_type' => TransactionType::class,
        'status' => TransactionStatus::class,
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
