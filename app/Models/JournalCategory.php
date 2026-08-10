<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Illuminate\Support\Str::slug($category->name);
            }
        });
    }

    public function journals()
    {
        return $this->hasMany(Journal::class);
    }
}
