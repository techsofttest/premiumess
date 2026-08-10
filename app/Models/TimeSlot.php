<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = ['delivery_date_id', 'start_time', 'end_time'];
    
    public function deliveryDate()
    {
        return $this->belongsTo(DeliveryDate::class);
    }
}
