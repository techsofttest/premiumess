<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuratedDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'subtitle',
        'description',
        'image',
        'price',
        'original_price',
        'discount_percent',
        'stock',
        'badge',
        'contents',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'original_price' => 'float',
        'discount_percent' => 'integer',
        'stock' => 'integer',
        'contents' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
