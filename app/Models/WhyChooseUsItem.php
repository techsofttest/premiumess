<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyChooseUsItem extends Model
{
    protected $table = 'why_choose_us_items';

    protected $fillable = [
        'title',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
