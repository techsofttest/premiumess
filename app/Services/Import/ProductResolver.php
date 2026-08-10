<?php

namespace App\Services\Import;

use App\Models\Product;

class ProductResolver
{
    /** @var array<string, Product> Keyed by lowercase SKU */
    protected array $cache = [];

    /**
     * Pre-load all existing products into the cache.
     */
    public function warmCache(): void
    {
        $this->cache = [];
        foreach (Product::all() as $product) {
            $this->cache[strtolower(trim($product->sku))] = $product;
        }
    }

    /**
     * Resolve a product by SKU. Creates or updates.
     * Same Product SKU may appear on multiple rows (one per variant).
     *
     * @param array<string, mixed> $attributes
     * @return array{product: Product, is_new: bool}
     */
    public function resolve(array $attributes): array
    {
        $sku = strtolower(trim($attributes['sku']));

        // Return from cache if already resolved in this import run
        if (isset($this->cache[$sku])) {
            $product = $this->cache[$sku];
            $product->update($attributes);
            return ['product' => $product, 'is_new' => false];
        }

        // Use updateOrCreate to safely handle duplicates at DB level
        $isNew = !Product::where('sku', trim($attributes['sku']))->exists();

        $product = Product::updateOrCreate(
            ['sku' => trim($attributes['sku'])],
            $attributes
        );

        $this->cache[$sku] = $product;

        return ['product' => $product, 'is_new' => $isNew];
    }
}
