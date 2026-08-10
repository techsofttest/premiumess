<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Log;

class ImportLogger
{
    protected int $totalRows = 0;
    protected int $importedProducts = 0;
    protected int $updatedProducts = 0;
    protected int $importedVariants = 0;
    protected int $updatedVariants = 0;
    protected int $newBrands = 0;
    protected int $newCategories = 0;
    protected int $newSubcategories = 0;
    protected int $missingImages = 0;
    protected int $failedRows = 0;

    /** @var array<string> */
    protected array $warnings = [];

    /** @var array<string> */
    protected array $errors = [];

    public function incrementTotalRows(): void { $this->totalRows++; }
    public function incrementImportedProducts(): void { $this->importedProducts++; }
    public function incrementUpdatedProducts(): void { $this->updatedProducts++; }
    public function incrementImportedVariants(): void { $this->importedVariants++; }
    public function incrementUpdatedVariants(): void { $this->updatedVariants++; }
    public function incrementNewBrands(): void { $this->newBrands++; }
    public function incrementNewCategories(): void { $this->newCategories++; }
    public function incrementNewSubcategories(): void { $this->newSubcategories++; }
    public function incrementMissingImages(): void { $this->missingImages++; }
    public function incrementFailedRows(): void { $this->failedRows++; }

    public function addWarning(string $message): void
    {
        $this->warnings[] = $message;
        Log::warning('[ProductImport] ' . $message);
    }

    public function addError(string $message): void
    {
        $this->errors[] = $message;
        Log::error('[ProductImport] ' . $message);
    }

    /**
     * Get the import summary.
     *
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'imported_products' => $this->importedProducts,
            'updated_products' => $this->updatedProducts,
            'imported_variants' => $this->importedVariants,
            'updated_variants' => $this->updatedVariants,
            'new_brands' => $this->newBrands,
            'new_categories' => $this->newCategories,
            'new_subcategories' => $this->newSubcategories,
            'missing_images' => $this->missingImages,
            'failed_rows' => $this->failedRows,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ];
    }

    /**
     * Get a formatted summary string.
     */
    public function getFormattedSummary(): string
    {
        $s = $this->getSummary();

        return sprintf(
            "Import Summary: %d rows processed. Products: %d new, %d updated. Variants: %d new, %d updated. " .
            "New Brands: %d, New Categories: %d, New Subcategories: %d. Missing Images: %d. Failed: %d.",
            $s['total_rows'],
            $s['imported_products'],
            $s['updated_products'],
            $s['imported_variants'],
            $s['updated_variants'],
            $s['new_brands'],
            $s['new_categories'],
            $s['new_subcategories'],
            $s['missing_images'],
            $s['failed_rows']
        );
    }
}
