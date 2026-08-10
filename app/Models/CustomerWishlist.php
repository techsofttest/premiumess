<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerWishlist extends Model
{
    protected $table = 'customer_wishlists';

    protected $fillable = [
        'customer_id',
        'product_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
