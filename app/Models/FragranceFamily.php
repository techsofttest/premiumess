<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FragranceFamily extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'description'];

    protected static function booted(): void
    {
        static::saving(function (self $family) {
            $slug = Str::slug($family->name);
            $originalSlug = $slug;
            $suffix = 1;
            while (static::where('slug', $slug)->whereKeyNot($family->id)->exists()) {
                $slug = $originalSlug . '-' . $suffix++;
            }
            $family->slug = $slug;
        });
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'fragrance_family_product');
    }
}
