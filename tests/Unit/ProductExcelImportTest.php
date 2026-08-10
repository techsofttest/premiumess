<?php

namespace Tests\Unit;

use App\Imports\ProductExcelImport;
use Tests\TestCase;

class ProductExcelImportTest extends TestCase
{
    public function test_map_row_includes_supplier_code_and_stock(): void
    {
        $import = new ProductExcelImport();
        $method = new \ReflectionMethod($import, 'mapRow');
        $method->setAccessible(true);

        $mapped = $method->invoke($import, [
            'product_sku' => 'SKU-001',
            'product_name' => 'Test Product',
            'brand' => 'Test Brand',
            'category' => 'Food',
            'sub_category' => 'Spices',
            'variant_sku' => 'VAR-001',
            'supplier_code' => 'SUP-001',
            'stock' => 25,
        ]);

        $this->assertSame('SUP-001', $mapped['supplier_code']);
        $this->assertSame(25, $mapped['stock']);
    }

    public function test_map_row_preserves_expiry_date_value(): void
    {
        $import = new ProductExcelImport();
        $method = new \ReflectionMethod($import, 'mapRow');
        $method->setAccessible(true);

        $mapped = $method->invoke($import, [
            'product_sku' => 'SKU-002',
            'product_name' => 'Test Product 2',
            'brand' => 'Test Brand',
            'category' => 'Food',
            'sub_category' => 'Spices',
            'variant_sku' => 'VAR-002',
            'expiry_date' => '2026-12-31',
        ]);

        $this->assertSame('2026-12-31', $mapped['expiry_date']);
    }

    public function test_parse_date_value_supports_excel_serial_dates(): void
    {
        $service = new \App\Services\Import\ProductImportService(
            new \App\Services\Import\BrandResolver(),
            new \App\Services\Import\CategoryResolver(),
            new \App\Services\Import\ProductResolver(),
            new \App\Services\Import\VariantResolver(),
            new \App\Services\Import\ImageResolver(),
            new \App\Services\Import\PriceCalculator(),
            new \App\Services\Import\ImportLogger()
        );

        $method = new \ReflectionMethod($service, 'parseDateValue');
        $method->setAccessible(true);

        $result = $method->invoke($service, 45292);

        $this->assertSame('2024-01-01', $result);
    }
}
