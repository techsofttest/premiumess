<?php

namespace Database\Seeders;

use App\Models\CuratedDeal;
use Illuminate\Database\Seeder;

class CuratedDealSeeder extends Seeder
{
    public function run(): void
    {
        $deals = [
            [
                'slug' => 'discovery-set',
                'name' => 'The Signature Discovery Set',
                'subtitle' => '5 x 10ml Luxury Travel Sprays',
                'description' => 'An exquisite curation of 5 iconic 10ml travel sprays featuring our master perfumers\' rarest floral, woody, and amber formulations. Crafted for connoisseurs exploring their signature scent.',
                'image' => 'deals/The Signature Discovery Set.png',
                'price' => 149.00,
                'original_price' => 199.00,
                'discount_percent' => 25,
                'badge' => 'Special Deal • Save 25%',
                'contents' => [
                    '1x 10ml Grand Floral Extrait de Parfum',
                    '1x 10ml Royal Amber Oud',
                    '1x 10ml Celestial Citrus Elixir',
                    '1x 10ml Velvet Vanilla Intense',
                    '1x 10ml Smokey Leather Essence',
                ],
                'features' => [
                    'Handcrafted 10ml atomizers with brass caps',
                    'Includes $50 voucher toward full 100ml bottle',
                    'Complimentary luxury gift box presentation',
                    'Worldwide express insured shipping',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'ultimate-box',
                'name' => 'Ultimate His & Hers Box',
                'subtitle' => 'Couples Deluxe Gift Set (2 x 100ml)',
                'description' => 'A harmonious couple\'s luxury gift box containing 1 Full-Size Men\'s Eau de Parfum (100ml) and 1 Full-Size Women\'s Extrait de Parfum (100ml) encased in a velvet presentation vault.',
                'image' => 'deals/Ultimate His & Hers Box.png',
                'price' => 289.00,
                'original_price' => 360.00,
                'discount_percent' => 20,
                'badge' => 'Exclusive Curation • Save 20%',
                'contents' => [
                    '1x 100ml Emperor Oud Pour Homme (Eau de Parfum)',
                    '1x 100ml Rose Royale Pour Femme (Extrait de Parfum)',
                    '1x Handcrafted Velvet Presentation Box',
                ],
                'features' => [
                    'Two full-size 100ml flagship fragrances',
                    'Personalized gold-embossed gift card',
                    'Long-lasting 12+ hour sillage guaranteed',
                    'Complimentary signature gift wrapping',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'oud-trio',
                'name' => 'Top Collection Trio',
                'subtitle' => 'Rare Oud Masterpieces (3 x 50ml)',
                'description' => 'The pinnacle of Middle-Eastern perfumery. Three handcrafted 50ml Extraits de Parfum celebrating pure Cambodian and Wild Assamese Oud aged for over 15 years.',
                'image' => 'deals/Top Collection Trio.png',
                'price' => 340.00,
                'original_price' => 450.00,
                'discount_percent' => 24,
                'badge' => 'Niche Curation • Save 24%',
                'contents' => [
                    '1x 50ml Imperial Oud (Extrait de Parfum)',
                    '1x 50ml Amber Wood Oud (Extrait de Parfum)',
                    '1x 50ml Rose & Oud Supreme (Extrait de Parfum)',
                ],
                'features' => [
                    '30% oil concentration (Extrait de Parfum)',
                    'Sourced from sustainable aged agarwood',
                    'Numbered collector\'s certificate included',
                    'Insured express courier delivery',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'travel-vault',
                'name' => 'Travel Miniatures Vault',
                'subtitle' => '4 x 15ml Refillable Atomizers',
                'description' => 'A sleek magnetic travel case holding 4 refillable 15ml purse atomizers designed for high-altitude voyages, evening galas, and seamless on-the-go luxury.',
                'image' => 'deals/travel_vault.png',
                'price' => 120.00,
                'original_price' => 160.00,
                'discount_percent' => 25,
                'badge' => 'Travel Exclusive • Save 25%',
                'contents' => [
                    '4x 15ml Refillable Miniature Spray Bottles',
                    '1x Hand-stitched Leather Magnetic Travel Case',
                    '1x Brass Transfer Funnel & Refill Adaptor',
                ],
                'features' => [
                    'TSA-approved carry-on size',
                    'Leakproof aircraft pressure tested',
                    'Easy 10-second refill funnel included',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'slug' => 'midnight-exclusive',
                'name' => 'Midnight Exclusives',
                'subtitle' => 'Seductive Nighttime Duo',
                'description' => 'Sensual, dark, and seductive evening fragrances formulated for mystery and allure after twilight. Rich notes of smoked vanilla, black rose, and dark amber.',
                'image' => 'deals/midnight_exclusive.png',
                'price' => 260.00,
                'original_price' => 320.00,
                'discount_percent' => 19,
                'badge' => 'Limited Edition • Save 19%',
                'contents' => [
                    '1x 100ml Midnight Amber (Extrait de Parfum)',
                    '1x 50ml Night Bloom Seduction (Parfum)',
                ],
                'features' => [
                    'Formulated specifically for evening longevity',
                    'Includes midnight black velvet travel pouch',
                    '100% cruelty-free & IFRA certified',
                ],
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($deals as $deal) {
            CuratedDeal::updateOrCreate(['slug' => $deal['slug']], $deal);
        }
    }
}
