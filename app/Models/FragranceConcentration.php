<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FragranceConcentration extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'description'];

    protected static function booted(): void
    {
        static::saving(function (self $concentration) {
            $slug = Str::slug($concentration->name);
            $originalSlug = $slug;
            $suffix = 1;
            while (static::where('slug', $slug)->whereKeyNot($concentration->id)->exists()) {
                $slug = $originalSlug . '-' . $suffix++;
            }
            $concentration->slug = $slug;
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'fragrance_concentration_id');
    }
}
