<?php

namespace App\Services\Import;

use App\Models\ProductVariant;

class VariantResolver
{
    /** @var array<string, ProductVariant> Keyed by lowercase "productId:sku" */
    protected array $cache = [];

    /**
     * Pre-load all existing variants into the cache.
     */
    public function warmCache(): void
    {
        $this->cache = [];
        foreach (ProductVariant::all() as $variant) {
            $this->cache[$this->cacheKey((int) $variant->product_id, (string) $variant->sku)] = $variant;
        }
    }

    /**
     * Resolve a variant by SKU. Creates or updates.
     *
     * @param array<string, mixed> $attributes Must include 'sku' and 'product_id'
     * @return array{variant: ProductVariant, is_new: bool}
     */
    public function resolve(array $attributes): array
    {
        $productId = (int) ($attributes['product_id'] ?? 0);
        $sku = trim((string) ($attributes['sku'] ?? ''));
        $cacheKey = $this->cacheKey($productId, $sku);

        if (isset($this->cache[$cacheKey])) {
            $variant = $this->cache[$cacheKey];
            $variant->update($attributes);
            return ['variant' => $variant, 'is_new' => false];
        }

        $isNew = !ProductVariant::where('product_id', $productId)
            ->where('sku', $sku)
            ->exists();

        $variant = ProductVariant::updateOrCreate(
            [
                'product_id' => $productId,
                'sku' => $sku,
            ],
            $attributes
        );

        $this->cache[$cacheKey] = $variant;

        return ['variant' => $variant, 'is_new' => $isNew];
    }

    protected function cacheKey(int $productId, string $sku): string
    {
        return $productId . ':' . strtolower(trim($sku));
    }
}
