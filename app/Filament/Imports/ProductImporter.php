<?php

namespace App\Filament\Imports;

use App\Models\Product;
use App\Services\Import\ProductImportService;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('product_sku')
                ->label('Product SKU')
                ->rules(['required', 'string']),

            ImportColumn::make('product_name')
                ->label('Product Name')
                ->rules(['required', 'string']),

            ImportColumn::make('brand')
                ->label('Brand')
                ->rules(['required', 'string']),

            ImportColumn::make('category')
                ->label('Category')
                ->rules(['required', 'string']),

            ImportColumn::make('sub_category')
                ->label('Sub Category')
                ->rules(['required', 'string']),

            ImportColumn::make('variant_sku')
                ->label('Variant SKU')
                ->rules(['required', 'string']),

            ImportColumn::make('unit')
                ->label('Unit'),

            ImportColumn::make('size')
                ->label('Size'),

            ImportColumn::make('buying_price')
                ->label('Buying Price')
                ->rules(['required', 'numeric']),

            ImportColumn::make('gst')
                ->label('GST'),

            ImportColumn::make('margin')
                ->label('Margin %'),

            ImportColumn::make('selling_price')
                ->label('Selling Price (ignored)'),

            ImportColumn::make('stock')
                ->label('Stock'),

            ImportColumn::make('key_features')
                ->label('Key Features'),

            ImportColumn::make('product_description')
                ->label('Product Description'),

            ImportColumn::make('expiry_date')
                ->label('Expiry Date'),

            ImportColumn::make('featured_image')
                ->label('Featured Image'),

            ImportColumn::make('additional_images')
                ->label('Additional Images'),

            ImportColumn::make('featured')
                ->label('Featured'),

            ImportColumn::make('seo_title')
                ->label('SEO Title'),

            ImportColumn::make('seo_description')
                ->label('SEO Description'),

            ImportColumn::make('courier')
                ->label('Courier'),
        ];
    }

    /**
     * Override the entire invocation to bypass Filament's default
     * fill/save lifecycle. We only need column remapping, then
     * delegate everything to ProductImportService.
     */
    public function __invoke(array $data): void
    {
        $this->originalData = $this->data = $data;

        // Remap Excel headers → our ImportColumn names
        $this->remapData();

        // Delegate entirely to our service
        $service = app(ProductImportService::class);
        $service->processRow($this->data);
    }

    /**
     * Required by the base class but never called due to __invoke override.
     */
    public function resolveRecord(): ?Product
    {
        return null;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successCount = $import->successful_rows;
        $failedCount = $import->getFailedRowsCount();

        $body = "Product import completed. {$successCount} rows imported successfully.";

        if ($failedCount > 0) {
            $body .= " {$failedCount} rows failed.";
        }

        return $body;
    }
}
