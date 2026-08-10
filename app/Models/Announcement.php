<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'text',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
