<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $table = 'contact_settings';

    protected $fillable = [
        'company_name',
        'address',
        'phone',
        'telephone',
        'whatsapp',
        'email',
        'support_email',
        'working_hours',
        'google_maps_link',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
        'youtube_url',
        'tiktok_url',
        'pinterest_url',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Premium Essence Perfumes LLC',
                'address' => 'Musaffah M/9, Abu Dhabi, United Arab Emirates',
                'phone' => '+971 55 723 2010',
                'telephone' => '02 550 8990',
                'whatsapp' => '+971 50 123 4567',
                'email' => 'info@premiumessence.ae',
                'support_email' => 'support@premiumessence.ae',
                'working_hours' => 'Mon - Sat: 9:00 AM - 9:00 PM (GST)',
                'google_maps_link' => 'https://maps.google.com/?q=Musaffah+M9+Abu+Dhabi+UAE',
                'facebook_url' => 'https://facebook.com',
                'instagram_url' => 'https://instagram.com',
                'twitter_url' => 'https://twitter.com',
                'linkedin_url' => 'https://linkedin.com',
                'youtube_url' => 'https://youtube.com',
                'tiktok_url' => 'https://tiktok.com',
                'pinterest_url' => 'https://pinterest.com',
            ]
        );
    }
}
