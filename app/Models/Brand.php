<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'classification',
        'logo',
        'description',
    ];

    protected static function booted()
    {
        static::saving(function ($brand) {
            $slug = Str::slug($brand->name);
            $originalSlug = $slug;
            $count = 1;
            while (static::where('slug', $slug)->where('id', '!=', $brand->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $brand->slug = $slug;
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
