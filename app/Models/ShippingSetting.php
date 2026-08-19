<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    protected $table = 'shipping_settings';

    protected $fillable = [
        'default_shipping_fee',
        'free_shipping_threshold',
        'is_enabled',
    ];

    protected $casts = [
        'default_shipping_fee' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'is_enabled' => 'boolean',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'default_shipping_fee' => 20.00,
                'free_shipping_threshold' => 200.00,
                'is_enabled' => true,
            ]
        );
    }
}
