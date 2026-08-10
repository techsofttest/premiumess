<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'name' => 'Best Sellers',
                'slug' => 'best-sellers',
                'description' => 'Our most popular and highest-rated luxury fragrances.',
                'sort_order' => 1,
            ],
            [
                'name' => 'New Arrivals',
                'slug' => 'new-arrivals',
                'description' => 'Latest additions to our prestige portfolio.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Trending',
                'slug' => 'trending',
                'description' => 'Trending perfumes loved across the region.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Featured',
                'slug' => 'featured',
                'description' => 'Handpicked featured scents for fine connoisseurs.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Special Deals',
                'slug' => 'special-deals',
                'description' => 'Exclusive offers and limited time discounts.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Best Choice',
                'slug' => 'best-choice',
                'description' => 'Editor recommended top picks.',
                'sort_order' => 6,
            ],
        ];

        foreach ($collections as $data) {
            Collection::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                    'sort_order' => $data['sort_order'],
                ]
            );
        }

        // Attach existing products to collections if not already attached
        $products = Product::where('is_active', true)->get();
        if ($products->count() > 0) {
            $bestSellers = Collection::where('slug', 'best-sellers')->first();
            $newArrivals = Collection::where('slug', 'new-arrivals')->first();
            $trending = Collection::where('slug', 'trending')->first();
            $featured = Collection::where('slug', 'featured')->first();
            $specialDeals = Collection::where('slug', 'special-deals')->first();
            $bestChoice = Collection::where('slug', 'best-choice')->first();

            foreach ($products as $index => $product) {
                // Featured products or all
                if ($product->is_featured && $featured) {
                    $featured->products()->syncWithoutDetaching([$product->id]);
                }
                // Distribute evenly across collections so every collection has items
                if ($bestSellers && ($product->is_featured || $index % 2 === 0)) {
                    $bestSellers->products()->syncWithoutDetaching([$product->id]);
                }
                if ($newArrivals && $index % 2 === 1) {
                    $newArrivals->products()->syncWithoutDetaching([$product->id]);
                }
                if ($trending && $index % 3 === 0) {
                    $trending->products()->syncWithoutDetaching([$product->id]);
                }
                if ($specialDeals && $index % 4 === 0) {
                    $specialDeals->products()->syncWithoutDetaching([$product->id]);
                }
                if ($bestChoice && ($product->is_featured || $index % 5 === 0)) {
                    $bestChoice->products()->syncWithoutDetaching([$product->id]);
                }
            }
        }
    }
}
