<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontProductsMinCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_products_fallback_to_global_products_when_count_is_below_minimum(): void
    {
        $category = Category::create([
            'name' => 'Personal Care',
            'slug' => 'personal-care',
            'is_active' => true,
        ]);

        $otherCategory = Category::create([
            'name' => 'Other',
            'slug' => 'other',
            'is_active' => true,
        ]);

        $brand = Brand::create([
            'name' => 'Brand A',
            'slug' => 'brand-a',
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $product = Product::create([
                'sku' => 'CAT-' . $i,
                'name' => 'Category Product ' . $i,
                'slug' => 'category-product-' . $i,
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'is_active' => true,
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'VAR-' . $i,
                'stock' => 5,
                'selling_price' => 10,
            ]);
        }

        for ($i = 1; $i <= 18; $i++) {
            $product = Product::create([
                'sku' => 'GLOBAL-' . $i,
                'name' => 'Global Product ' . $i,
                'slug' => 'global-product-' . $i,
                'brand_id' => $brand->id,
                'category_id' => $otherCategory->id,
                'is_active' => true,
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'GVAR-' . $i,
                'stock' => 5,
                'selling_price' => 10,
            ]);
        }

        $response = $this->getJson('/api/storefront/products?category=personal-care&per_page=18');

        $response->assertOk();
        $response->assertJsonCount(18, 'data');
        $this->assertSame(2, collect($response->json('data'))->filter(fn ($item) => $item['category']['slug'] === 'personal-care')->count());
    }
}
