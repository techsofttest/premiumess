<?php

namespace App\Imports;

use App\Services\Import\ProductImportService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductExcelImport implements ToArray, WithHeadingRow, WithChunkReading
{
    protected ProductImportService $service;
    protected int $processed = 0;
    protected int $failed = 0;

    public function __construct()
    {
        $this->service = app(ProductImportService::class);
    }

    /**
     * Map Excel heading row keys (auto-slugified by maatwebsite/excel)
     * to the keys our ProductImportService expects.
     *
     * maatwebsite/excel converts headers like "Product SKU" → "product_sku",
     * "Varient SKU" → "varient_sku", "Margin%" → "margin", etc.
     */
    protected function mapRow(array $excelRow): array
    {
        return [
            'product_sku'         => trim((string) ($excelRow['product_sku'] ?? '')),
            'product_name'        => trim((string) ($excelRow['product_name'] ?? '')),
            'brand'               => trim((string) ($excelRow['brand'] ?? '')),
            'category'            => trim((string) ($excelRow['category'] ?? '')),
            'sub_category'        => trim((string) ($excelRow['sub_category'] ?? '')),
            'variant_sku'         => trim((string) ($excelRow['varient_sku'] ?? $excelRow['variant_sku'] ?? '')),
            'supplier_code'       => trim((string) ($excelRow['supplier_code'] ?? $excelRow['supplier'] ?? '')),
            'unit'                => $excelRow['unit'] ?? null,
            'size'                => $excelRow['size'] ?? null,
            'buying_price'        => $excelRow['buying_price'] ?? null,
            'gst'                 => $excelRow['gst'] ?? null,
            'margin'              => $excelRow['margin'] ?? $excelRow['margin_percent'] ?? null,
            'stock'               => $excelRow['stock'] ?? 0,
            'key_features'        => $excelRow['key_features'] ?? null,
            'product_description' => $excelRow['product_description'] ?? null,
            'expiry_date'         => $excelRow['expiry_date'] ?? null,
            'featured_image'      => $excelRow['featured_image'] ?? null,
            'additional_images'   => $excelRow['additional_images'] ?? null,
            'featured'            => $excelRow['featured'] ?? null,
            'seo_title'           => $excelRow['seo_title'] ?? null,
            'seo_description'     => $excelRow['seo_description'] ?? null,
            'courier'             => $excelRow['courier'] ?? null,
        ];
    }

    public function array(array $rows): void
    {
        foreach ($rows as $excelRow) {
            // Skip completely empty rows
            $productSku = trim((string) ($excelRow['product_sku'] ?? ''));
            if (empty($productSku)) {
                continue;
            }

            try {
                $mapped = $this->mapRow($excelRow);
                $this->service->processRow($mapped);
                $this->processed++;
            } catch (\Exception $e) {
                $this->failed++;
                Log::error("[ProductExcelImport] Row failed for SKU {$productSku}: " . $e->getMessage());
            }
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getProcessedCount(): int
    {
        return $this->processed;
    }

    public function getFailedCount(): int
    {
        return $this->failed;
    }

    public function getService(): ProductImportService
    {
        return $this->service;
    }
}
