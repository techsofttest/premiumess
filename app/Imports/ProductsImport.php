<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\Str;

class ProductsImport implements ToCollection, WithStartRow
{
    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Assuming the first row is headings
    }

    /**
     * @param LaravelCollection $rows
     */
    public function collection(LaravelCollection $rows)
    {
        foreach ($rows as $index => $row) {
            // Trim names to handle extra spaces
            $productSku     = trim($row[0] ?? '');
            $productName    = trim($row[1] ?? '');
            $brand          = trim($row[2] ?? '');
            $supplierCode   = trim($row[3] ?? '');
            $stockInOrder   = intval(trim($row[4] ?? 0));
            $stockOnHand    = intval(trim($row[5] ?? 0));
            $categoryName   = trim($row[6] ?? '');
            $subCategoryName = trim($row[7] ?? '');
            $variantSku     = trim($row[8] ?? '');
            $unit           = trim($row[9] ?? '');
            $size           = trim($row[10] ?? '');
            $buyingPrice    = floatval(trim($row[11] ?? 0));
            $margin         = floatval(trim($row[12] ?? 0));
            $sellingPrice   = floatval(trim($row[13] ?? 0));
            $variantStock   = intval(trim($row[14] ?? 0));
            $keyFeatures    = trim($row[15] ?? '');
            $description    = trim($row[16] ?? '');
            $expiryDate     = trim($row[17] ?? '');
            $featuredImage  = trim($row[18] ?? '');
            $additionalImages = trim($row[19] ?? '');
            $isFeatured     = trim($row[20] ?? ''); // e.g. 1 or 0
            $seoTitle       = trim($row[21] ?? '');
            $seoDescription = trim($row[22] ?? '');

            // Skip empty rows
            if (empty($productName)) {
                continue;
            }

            // Map Category IDs based on names (case-insensitive)
            $catId = null;
            if ($categoryName) {
                // Find or create Parent Category
                $category = Category::firstOrCreate(
                    ['name' => $categoryName, 'parent_id' => null],
                    ['slug' => Str::slug($categoryName), 'is_active' => true]
                );
                $catId = $category->id;
                
                // If subcategory is provided, find or create it under the parent
                if ($subCategoryName) {
                    $subCategory = Category::firstOrCreate(
                        ['name' => $subCategoryName, 'parent_id' => $category->id],
                        ['slug' => Str::slug($categoryName . '-' . $subCategoryName), 'is_active' => true]
                    );
                    $catId = $subCategory->id; // Associate product with the most specific child category
                }
            }

            // Identify product (update or create)
            $productData = [
                'sku'              => $productSku ?: null,
                'brand'            => $brand ?: null,
                'supplier_code'    => $supplierCode ?: null,
                'category_id'      => $catId,
                'stock_in_order'   => $stockInOrder,
                'stock_on_hand'    => $stockOnHand,
                'key_features'     => $keyFeatures ?: null,
                'description'      => $description ?: null,
                'expiry_date'      => $expiryDate ? date('Y-m-d', strtotime($expiryDate)) : null,
                'featured_image'   => $featuredImage ?: null,
                'is_featured'      => $isFeatured == '1' || strtolower($isFeatured) == 'yes',
                'meta_title'       => $seoTitle ?: null,
                'meta_description' => $seoDescription ?: null,
                'is_active'        => true,
            ];

            // If it's a new product, we need a slug
            $product = Product::where('name', $productName)->first();
            if ($product) {
                $product->update($productData);
            } else {
                $productData['name'] = $productName;
                $productData['slug'] = Str::slug($productName);
                $product = Product::create($productData);
            }

            // Handle Variants (Unit/Size)
            ProductVariant::updateOrCreate(
                [
                    'product_id' => $product->id, 
                    'sku' => $variantSku ?: null, 
                    'unit' => $unit ?: null, 
                    'size' => $size ?: null
                ],
                [
                    'buying_price'  => $buyingPrice,
                    'margin'        => $margin,
                    'selling_price' => $sellingPrice,
                    'stock'         => $variantStock,
                ]
            );

            // Handle Additional Images
            if ($additionalImages) {
                $images = array_filter(array_map('trim', explode(',', $additionalImages)));
                foreach ($images as $img) {
                    ProductImage::updateOrCreate([
                        'product_id' => $product->id,
                        'image_path' => $img
                    ]);
                }
            }
        }
    }
}
