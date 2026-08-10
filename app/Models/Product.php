<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'slug',
        'brand_id',
        'supplier_code',
        'category_id',
        'key_features',
        'description',
        'featured_image',
        'is_featured',
        'is_active',
        'requires_direct_delivery',
        'allows_courier',
        'meta_title',
        'meta_description',
        'gender',
        'fragrance_family_id',
        'fragrance_concentration_id',
        'top_notes',
        'middle_notes',
        'base_notes',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'requires_direct_delivery' => 'boolean',
        'allows_courier' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($product) {
            $slug = Str::slug($product->name);
            $originalSlug = $slug;
            $count = 1;
            while (static::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $product->slug = $slug;
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'review_product_id');
    }

    /**
     * Get the cheapest variant's selling price
     */
    public function getMinPriceAttribute()
    {
        return $this->variants->min('selling_price');
    }

    /**
     * Get the most expensive variant's selling price
     */
    public function getMaxPriceAttribute()
    {
        return $this->variants->max('selling_price');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function fragranceFamily()
    {
        return $this->belongsTo(FragranceFamily::class);
    }

    public function fragranceConcentration()
    {
        return $this->belongsTo(FragranceConcentration::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_product');
    }
}
