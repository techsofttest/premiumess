<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageSection extends Model
{
    public const TYPE_PROMO_SLIDER = 'promo_slider';
    public const TYPE_PRODUCT_ROW = 'product_row';
    public const TYPE_CATEGORY_GRID = 'category_grid';
    public const TYPE_SUB_CATEGORIES = 'sub_categories';
    public const TYPE_BRANDS = 'brands';
    public const TYPE_ADVERTISEMENT = 'advertisement';
    public const TYPE_TOP_OFFERS = 'top_offers';

    public const SOURCE_BANNERS = 'banners';
    public const SOURCE_LATEST_PRODUCTS = 'latest_products';
    public const SOURCE_FEATURED_PRODUCTS = 'featured_products';
    public const SOURCE_TOP_OFFERS = 'top_offers';
    public const SOURCE_CATEGORIES = 'categories';
    public const SOURCE_BRANDS = 'brands';
    public const SOURCE_CUSTOM_PRODUCTS = 'custom_products';
    public const SOURCE_CUSTOM_CATEGORIES = 'custom_categories';
    public const SOURCE_CUSTOM_BRANDS = 'custom_brands';
    public const SOURCE_ADVERTISEMENT = 'advertisement';

    protected $fillable = [
        'name',
        'type',
        'source',
        'title',
        'subtitle',
        'link_label',
        'link_url',
        'background_color',
        'item_limit',
        'product_ids',
        'category_ids',
        'brand_ids',
        'settings',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'product_ids' => 'array',
        'category_ids' => 'array',
        'brand_ids' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'item_limit' => 'integer',
        'sort_order' => 'integer',
    ];
}
