<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Journal extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'content',
        'image',
        'author',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($journal) {
            if (empty($journal->slug)) {
                $slug = Str::slug($journal->title);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->where('id', '!=', $journal->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $journal->slug = $slug;
            }
            if ($journal->is_published && empty($journal->published_at)) {
                $journal->published_at = now();
            }
        });
    } 
}
