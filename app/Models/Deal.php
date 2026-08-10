<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $fillable = [
        'deal_product_id',
        'deal_variant_id',
        'deal_price',
        'deal_label',
        'deal_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'deal_product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'deal_variant_id');
    }
}
