<?php 

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'google_id',
        'profile_image',
        'last_login_at',
        'status',
        'otp',
        'otp_expires_at',
        'default_shipping_address_id',
        'default_billing_address_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function defaultShippingAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'default_shipping_address_id');
    }

    public function defaultBillingAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'default_billing_address_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function cart()
    {
        return $this->hasOne(CustomerCart::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(CustomerWishlist::class, 'customer_id');
    }

    public function wishlistedProducts()
    {
        return $this->belongsToMany(Product::class, 'customer_wishlists', 'customer_id', 'product_id');
    }
}
