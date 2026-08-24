<?php

namespace Database\Seeders;

use App\Models\Seo;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seoRecords = [
            [
                'title' => 'Home Page',
                'page_slug' => 'home',
                'meta_title' => 'Premium Essence | Luxury Perfumes & Fragrances UAE',
                'meta_description' => 'Discover exclusive luxury perfumes, haute parfumerie, and authentic niche fragrances in Abu Dhabi and Dubai with express UAE delivery.',
                'meta_keywords' => 'perfumes, luxury fragrances, UAE, Abu Dhabi, Dubai, niche perfumes, Oud, Eau de Parfum',
            ],
            [
                'title' => 'About Us',
                'page_slug' => 'about-us',
                'meta_title' => 'About Us | Premium Essence Perfumes',
                'meta_description' => "Learn about Premium Essence's heritage, our commitment to 100% authentic European and Middle Eastern luxury perfume houses.",
                'meta_keywords' => 'about premium essence, luxury perfume house, authentic perfumes UAE, perfume heritage',
            ],
            [
                'title' => 'Contact Us',
                'page_slug' => 'contact-us',
                'meta_title' => 'Contact Us | Premium Essence Concierge',
                'meta_description' => 'Reach out to our expert perfume advisors and concierge team in Abu Dhabi for custom fragrance advice and assistance.',
                'meta_keywords' => 'contact premium essence, perfume concierge, Abu Dhabi perfume shop, customer support',
            ],
            [
                'title' => 'Shop / Fragrances',
                'page_slug' => 'shop',
                'meta_title' => 'Shop Luxury Fragrances | Premium Essence',
                'meta_description' => 'Browse our entire curated catalog of authentic European, Oriental, Extrait, and Eau de Parfum fragrances.',
                'meta_keywords' => 'shop perfumes, buy fragrance UAE, oriental scents, unisex perfume, men cologne, women perfume',
            ],
            [
                'title' => 'Brands',
                'page_slug' => 'brands',
                'meta_title' => 'Luxury Fragrance Brands | Premium Essence',
                'meta_description' => 'Explore prestigious perfume houses, artisanal perfumers, and classic elegance brands in our portfolio.',
                'meta_keywords' => 'perfume brands, Creed, Amouage, Tom Ford, Roja Parfums, luxury brands UAE',
            ],
            [
                'title' => 'Frequently Asked Questions',
                'page_slug' => 'faqs',
                'meta_title' => 'Frequently Asked Questions | Premium Essence',
                'meta_description' => 'Find answers regarding fragrance authenticity, delivery times, return policies, and perfume care.',
                'meta_keywords' => 'perfume FAQ, delivery UAE, return policy, perfume authenticity',
            ],
            [
                'title' => 'The Olfactory Journal',
                'page_slug' => 'journals',
                'meta_title' => 'The Perfume Journal | Olfactory Insights',
                'meta_description' => 'Read deep dives into rare ingredients, scent accords, master perfumer notes, and haute parfumerie history.',
                'meta_keywords' => 'perfume blog, fragrance guides, olfactory notes, scent layering, perfumer journal',
            ],
            [
                'title' => 'My Wishlist',
                'page_slug' => 'wishlist',
                'meta_title' => 'My Wishlist | Premium Essence',
                'meta_description' => 'View and manage your saved favorite luxury fragrances.',
                'meta_keywords' => 'wishlist, saved perfumes, luxury wishlist',
            ],
            [
                'title' => 'Shopping Cart',
                'page_slug' => 'cart',
                'meta_title' => 'Shopping Cart | Premium Essence',
                'meta_description' => 'Review your selected luxury fragrances and proceed to secure checkout.',
                'meta_keywords' => 'cart, checkout, buy perfumes',
            ],
            [
                'title' => 'Privacy Policy',
                'page_slug' => 'privacy-policy',
                'meta_title' => 'Privacy Policy | Premium Essence Perfumes',
                'meta_description' => 'Read our privacy policy regarding customer data protection and secure encrypted transactions.',
                'meta_keywords' => 'privacy policy, data protection, secure shopping',
            ],
            [
                'title' => 'Terms & Conditions',
                'page_slug' => 'terms-and-conditions',
                'meta_title' => 'Terms & Conditions | Premium Essence Perfumes',
                'meta_description' => 'Review the terms and conditions governing purchases and website usage at Premium Essence.',
                'meta_keywords' => 'terms and conditions, store policy, legal terms',
            ],
            [
                'title' => 'Shipping & Delivery Policy',
                'page_slug' => 'shipping-policy',
                'meta_title' => 'Shipping & Delivery Policy | Premium Essence',
                'meta_description' => 'Details on same-day dispatch, Abu Dhabi direct delivery, and courier shipping across all 7 Emirates.',
                'meta_keywords' => 'shipping policy, UAE delivery, Abu Dhabi delivery, express shipping',
            ],
            [
                'title' => 'Refund & Return Policy',
                'page_slug' => 'refund-and-return',
                'meta_title' => 'Refund & Return Policy | Premium Essence',
                'meta_description' => 'Information about our 14-day return and exchange policy for unopened luxury items.',
                'meta_keywords' => 'returns, refund policy, exchange perfume',
            ],
            [
                'title' => 'Bestselling Fragrances',
                'page_slug' => 'bestsellers',
                'meta_title' => 'Bestselling Luxury Perfumes | Premium Essence',
                'meta_description' => 'Discover the most sought-after and top-rated luxury fragrances across the UAE.',
                'meta_keywords' => 'bestseller perfumes, top fragrances, popular cologne UAE',
            ],
            [
                'title' => 'New Arrivals',
                'page_slug' => 'new-arrivals',
                'meta_title' => 'New Fragrance Arrivals | Premium Essence',
                'meta_description' => 'Explore the newest luxury perfume releases, seasonal editions, and fresh arrivals.',
                'meta_keywords' => 'new perfumes, fresh arrivals, latest fragrance release',
            ],
        ];

        foreach ($seoRecords as $record) {
            Seo::updateOrCreate(
                ['page_slug' => $record['page_slug']],
                $record
            );
        }
    }
}
