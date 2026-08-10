<?php

namespace App\Services\Import;

use App\Models\ProductImage;

class ProductImportService
{
    protected BrandResolver $brandResolver;
    protected CategoryResolver $categoryResolver;
    protected ProductResolver $productResolver;
    protected VariantResolver $variantResolver;
    protected ImageResolver $imageResolver;
    protected PriceCalculator $priceCalculator;
    protected ImportLogger $logger;

    protected bool $warmed = false;

    public function __construct(
        BrandResolver $brandResolver,
        CategoryResolver $categoryResolver,
        ProductResolver $productResolver,
        VariantResolver $variantResolver,
        ImageResolver $imageResolver,
        PriceCalculator $priceCalculator,
        ImportLogger $logger
    ) {
        $this->brandResolver = $brandResolver;
        $this->categoryResolver = $categoryResolver;
        $this->productResolver = $productResolver;
        $this->variantResolver = $variantResolver;
        $this->imageResolver = $imageResolver;
        $this->priceCalculator = $priceCalculator;
        $this->logger = $logger;
    }

    /**
     * Warm all resolver caches. Should be called once before processing rows.
     */
    public function warmCaches(): void
    {
        if ($this->warmed) {
            return;
        }

        $this->brandResolver->warmCache();
        $this->categoryResolver->warmCache();
        $this->productResolver->warmCache();
        $this->variantResolver->warmCache();

        $this->warmed = true;
    }

    /**
     * Process a single import row.
     *
     * @param array<string, mixed> $row
     * @return void
     * @throws \Exception On critical validation failure
     */
    public function processRow(array $row): void
    {
        $this->warmCaches();
        $this->logger->incrementTotalRows();

        try {
            // Validate required fields
            $this->validateRow($row);

            // Resolve Brand
            $brand = $this->brandResolver->resolve($row['brand']);

            // Resolve Category + Subcategory
            $parentCategory = $this->categoryResolver->resolveParent($row['category']);
            $subCategory = $this->categoryResolver->resolveChild($row['sub_category'], $parentCategory);

            // Parse delivery flags
            $allowsCourier = $this->parseTruthy($row['courier'] ?? null);

            // Parse featured flag
            $isFeatured = $this->parseTruthy($row['featured'] ?? null);

            // Resolve Featured Image
            $featuredImagePath = null;
            if (!empty($row['featured_image'])) {
                $featuredImagePath = $this->imageResolver->resolve($row['featured_image']);
                if ($featuredImagePath === null) {
                    $this->logger->addWarning("Missing featured image: {$row['featured_image']} for product {$row['product_sku']}");
                    $this->logger->incrementMissingImages();
                }
            }

            // Resolve Product
            $productAttributes = [
                'sku' => trim($row['product_sku']),
                'name' => trim($row['product_name']),
                'brand_id' => $brand->id,
                'category_id' => $subCategory->id,
                'supplier_code' => $row['supplier_code'] ?? null,
                'key_features' => $row['key_features'] ?? null,
                'description' => $row['product_description'] ?? null,
                'is_featured' => $isFeatured,
                'is_active' => true,
                'requires_direct_delivery' => true,
                'allows_courier' => $allowsCourier,
                'meta_title' => $row['seo_title'] ?? null,
                'meta_description' => $row['seo_description'] ?? null,
            ];

            if ($featuredImagePath !== null) {
                $productAttributes['featured_image'] = $featuredImagePath;
            }

            $productResult = $this->productResolver->resolve($productAttributes);
            $product = $productResult['product'];

            if ($productResult['is_new']) {
                $this->logger->incrementImportedProducts();
            } else {
                $this->logger->incrementUpdatedProducts();
            }

            // Calculate selling price
            $buyingPrice = (float) ($row['buying_price'] ?? 0);
            $gstPercent = $this->priceCalculator->parseGst($row['gst'] ?? null);
            $marginPercent = (float) ($row['margin'] ?? 0);
            // GST is stored in `tax_percentage` but should not be applied during price
            // calculation because the margin from Excel already includes GST.
            $sellingPrice = $this->priceCalculator->calculate($buyingPrice, $marginPercent);

            // Resolve Variant
            $variantAttributes = [
                'product_id' => $product->id,
                'sku' => trim($row['variant_sku']),
                'unit' => $row['unit'] ?? null,
                'size' => $row['size'] ?? null,
                'buying_price' => $buyingPrice,
                'margin' => $marginPercent,
                'tax_percentage' => $gstPercent,
                'expiry_date' => $this->parseDateValue($row['expiry_date'] ?? null),
                'selling_price' => $sellingPrice,
                'stock' => (int) ($row['stock'] ?? 0),
            ];

            $variantResult = $this->variantResolver->resolve($variantAttributes);

            if ($variantResult['is_new']) {
                $this->logger->incrementImportedVariants();
            } else {
                $this->logger->incrementUpdatedVariants();
            }

            // Resolve Additional Images
            if (!empty($row['additional_images'])) {
                $additionalPaths = $this->imageResolver->resolveMultiple($row['additional_images']);

                // Remove old gallery images and recreate
                ProductImage::where('product_id', $product->id)->delete();

                foreach ($additionalPaths as $path) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                    ]);
                }

                // Log missing additional images
                $requested = array_map('trim', explode(',', $row['additional_images']));
                $missingCount = count($requested) - count($additionalPaths);
                if ($missingCount > 0) {
                    $this->logger->addWarning("Missing {$missingCount} additional image(s) for product {$row['product_sku']}");
                    for ($i = 0; $i < $missingCount; $i++) {
                        $this->logger->incrementMissingImages();
                    }
                }
            }

        } catch (\Exception $e) {
            $this->logger->incrementFailedRows();
            $this->logger->addError("Row failed for SKU {$row['product_sku']}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate required fields for a row.
     */
    protected function parseDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            $numericValue = (float) $value;

            if ($numericValue > 1000000000) {
                return \Carbon\Carbon::createFromTimestamp((int) $numericValue)->format('Y-m-d');
            }

            if ($numericValue > 0) {
                if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                    try {
                        $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numericValue);
                        if ($excelDate instanceof \DateTimeInterface) {
                            return $excelDate->format('Y-m-d');
                        }
                    } catch (\Throwable $e) {
                        // Fall back to a timestamp-based parse if the value is not an Excel serial date.
                    }
                }

                return \Carbon\Carbon::createFromTimestamp((int) $numericValue)->format('Y-m-d');
            }
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed)) {
                return $trimmed;
            }

            if (preg_match('/^\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4}$/', $trimmed)) {
                return \Carbon\Carbon::parse($trimmed)->format('Y-m-d');
            }

            if (preg_match('/^\d+$/', $trimmed)) {
                return \Carbon\Carbon::createFromTimestamp((int) $trimmed)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($trimmed)->format('Y-m-d');
        }

        return null;
    }

    protected function validateRow(array $row): void
    {
        $required = ['product_sku', 'product_name', 'brand', 'category', 'sub_category', 'variant_sku', 'buying_price'];

        foreach ($required as $field) {
            if (empty($row[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }

    /**
     * Parse a truthy value (y, yes, true, 1) from Excel.
     */
    protected function parseTruthy(mixed $value): bool
    {
        if (is_null($value) || $value === '') {
            return false;
        }

        return in_array(strtolower(trim((string) $value)), ['y', 'yes', 'true', '1'], true);
    }

    /**
     * Get the logger instance for summary retrieval.
     */
    public function getLogger(): ImportLogger
    {
        return $this->logger;
    }
}
