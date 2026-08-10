<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cms extends Model
{
    protected $table = 'cms';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'image',
        'meta_title',
        'description',
    ];
}
