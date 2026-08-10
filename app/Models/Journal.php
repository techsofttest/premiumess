<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Journal extends Model
{
    //
    protected $fillable = ['journal_category_id', 'title', 'label', 'content', 'date', 'image', 'slug','meta_title','meta_description','meta_keywords'];

    protected static function booted()
    {
        static::saving(function ($journal) {
            $slug = Str::slug($journal->title);
            $originalSlug = $slug;
            $count = 1;
            while (static::where('slug', $slug)->where('id', '!=', $journal->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $journal->slug = $slug;
        });
    }

    public function category()
    {
        return $this->belongsTo(JournalCategory::class, 'journal_category_id');
    }
}
