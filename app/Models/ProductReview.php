<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    //

    protected $fillable = [
        'review_product_id',
        'review_name',
        'review_email',
        'review_rating',
        'review_title',
        'review_content',
        'review_status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'review_product_id');
    }
}
