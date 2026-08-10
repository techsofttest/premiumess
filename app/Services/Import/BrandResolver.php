<?php

namespace App\Services\Import;

use App\Models\Brand;

class BrandResolver
{
    /** @var array<string, Brand> */
    protected array $cache = [];

    /**
     * Pre-load all existing brands into the cache.
     */
    public function warmCache(): void
    {
        $this->cache = Brand::all()->keyBy(fn (Brand $b) => strtolower(trim($b->name)))->toArray();
        $this->cache = Brand::all()->mapWithKeys(fn (Brand $b) => [strtolower(trim($b->name)) => $b])->toArray();

        // Re-hydrate as Brand models
        $this->cache = [];
        foreach (Brand::all() as $brand) {
            $this->cache[strtolower(trim($brand->name))] = $brand;
        }
    }

    /**
     * Resolve a brand by name. Creates if missing.
     *
     * @param string $name
     * @return Brand
     */
    public function resolve(string $name): Brand
    {
        $key = strtolower(trim($name));

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // Find or create
        $brand = Brand::where('name', trim($name))->first();

        if (!$brand) {
            $brand = Brand::create([
                'name' => trim($name),
            ]);
        }

        $this->cache[$key] = $brand;

        return $brand;
    }
}
