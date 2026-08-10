<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDate extends Model
{
    protected $fillable = ['date'];
    
    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }
}
