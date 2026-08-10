<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'label',
        'contact_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'suburb',
        'city',
        'state',
        'postcode',
        'country',
        'latitude',
        'longitude',
        'google_place_id',
        'delivery_notes',
        'is_default_shipping',
        'is_default_billing'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}