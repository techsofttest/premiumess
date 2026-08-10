<?php

namespace Database\Seeders;

use App\Models\Cms;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
            ],
            [
                'title' => 'Payments & OTP',
                'slug' => 'payments-otp',
            ],
            [
                'title' => 'Shipping & Dispatch',
                'slug' => 'shipping-dispatch',
            ],
            [
                'title' => 'Cancellation & Returns',
                'slug' => 'cancellation-returns',
            ],
            [
                'title' => 'Terms Of Use',
                'slug' => 'terms-of-use',
            ],
            [
                'title' => 'Security & Privacy',
                'slug' => 'security-privacy',
            ],
            [
                'title' => 'Refund Policy',
                'slug' => 'refund-policy',
            ],
            [
                'title' => 'Product Expiry Rules',
                'slug' => 'product-expiry-rules',
            ],
        ];

        foreach ($pages as $page) {
            Cms::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => "<h2>{$page['title']}</h2>\n<p>Content coming soon.</p>",
                    'image' => null,
                    'meta_title' => $page['title'],
                    'description' => null,
                ]
            );
        }
    }
}
