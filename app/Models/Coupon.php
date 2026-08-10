<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    //
    protected $fillable = [
        'coupon_code',
        'coupon_name',
        'coupon_type',
        'coupon_amount',
        'coupon_fromdate',
        'coupon_todate',
        'minimum_order_amount',
        'maximum_discount',
        'global_usage_limit',
        'customer_usage_limit',
        'first_order_only',
        'active',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}
