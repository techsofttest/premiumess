<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'product_name',
        'variant_details', 'quantity', 'price', 'line_total', 'is_picked'
    ];

    protected $casts = [
        'is_picked' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getCuratedDeal(): ?CuratedDeal
    {
        if (!empty($this->variant_id)) {
            return null;
        }

        if ($this->product_id) {
            $deal = CuratedDeal::find($this->product_id);
            if ($deal) return $deal;
        }

        if (!empty($this->product_name)) {
            $deal = CuratedDeal::where('name', $this->product_name)->first();
            if ($deal) return $deal;
        }

        return null;
    }
}
