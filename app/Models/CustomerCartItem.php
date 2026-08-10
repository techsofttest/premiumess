<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCartItem extends Model
{
    protected $fillable = ['customer_cart_id', 'product_id', 'product_variant_id', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(CustomerCart::class, 'customer_cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
