<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Import\VariantResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_resolution_is_scoped_per_product(): void
    {
        $productA = Product::create([
            'sku' => 'SKU-A',
            'name' => 'Product A',
        ]);

        $productB = Product::create([
            'sku' => 'SKU-B',
            'name' => 'Product B',
        ]);

        $resolver = new VariantResolver();

        $resolver->resolve([
            'product_id' => $productA->id,
            'sku' => 'VAR-1',
            'unit' => 'kg',
            'size' => '1kg',
            'buying_price' => 10,
            'margin' => 10,
            'selling_price' => 12,
            'stock' => 5,
        ]);

        $resolver->resolve([
            'product_id' => $productB->id,
            'sku' => 'VAR-1',
            'unit' => 'kg',
            'size' => '1kg',
            'buying_price' => 20,
            'margin' => 10,
            'selling_price' => 24,
            'stock' => 3,
        ]);

        $this->assertCount(2, ProductVariant::all());
        $this->assertEquals(1, $productA->fresh()->variants()->count());
        $this->assertEquals(1, $productB->fresh()->variants()->count());
    }
}
