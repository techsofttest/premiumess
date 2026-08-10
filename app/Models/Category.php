<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'image',
        'icon',
        'slug',
        'sort_order',
        'is_active',
        'home_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'home_featured' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($category) {
            $slug = Str::slug($category->name);
            $originalSlug = $slug;
            $count = 1;
            while (static::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $category->slug = $slug;
        });
    }

    /**
     * Parent category (null for top-level categories)
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Direct children (subcategories)
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Recursive children (all descendants)
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Products directly in this category
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Check if this is a top-level category
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a subcategory
     */
    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }
}
