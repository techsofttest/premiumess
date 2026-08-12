<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\FragranceConcentration;
use App\Models\FragranceFamily;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductCsvImporter
{
    public static function import(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Uploaded file could not be found.");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Failed to open uploaded file.");
        }

        // Remove UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detect delimiter (comma vs semicolon)
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") {
            fseek($handle, 3);
        }

        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            throw new \Exception("CSV file appears to be empty.");
        }

        // Normalize header keys (lowercase, trim)
        $header = array_map(function ($h) {
            return strtolower(trim(str_replace(['"', "'", "\xEF\xBB\xBF"], '', $h)));
        }, $header);

        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map row columns to header
            $data = [];
            foreach ($header as $index => $key) {
                $data[$key] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            $name = $data['name'] ?? $data['title'] ?? $data['product_name'] ?? '';
            if (empty($name)) {
                $errors[] = "Row {$rowNum}: Skipped because product name is empty.";
                continue;
            }

            try {
                $sku = $data['sku'] ?? $data['product_sku'] ?? (strtoupper(Str::slug($name)) . '-' . Str::random(4));

                // 1. Resolve Brand
                $brandId = null;
                $brandName = $data['brand'] ?? $data['brand_name'] ?? '';
                if (!empty($brandName)) {
                    $brand = Brand::firstOrCreate(
                        ['name' => $brandName],
                        ['slug' => Str::slug($brandName), 'is_active' => true]
                    );
                    $brandId = $brand->id;
                }

                // 2. Resolve Category
                $categoryId = null;
                $catName = $data['category'] ?? $data['category_name'] ?? '';
                if (!empty($catName)) {
                    $cat = Category::firstOrCreate(
                        ['name' => $catName],
                        ['slug' => Str::slug($catName), 'is_active' => true]
                    );
                    $categoryId = $cat->id;
                }

                // 3. Resolve Fragrance Concentration
                $concentrationId = null;
                $concName = $data['fragrance_concentration'] ?? $data['concentration'] ?? '';
                if (!empty($concName)) {
                    $conc = FragranceConcentration::firstOrCreate(
                        ['name' => $concName],
                        ['slug' => Str::slug($concName)]
                    );
                    $concentrationId = $conc->id;
                }

                // 4. Create or Update Product
                $product = Product::where('sku', $sku)
                    ->orWhere('name', $name)
                    ->first();

                $isNew = false;
                if (!$product) {
                    $product = new Product();
                    $isNew = true;
                }

                $product->sku = $sku;
                $product->name = $name;
                $product->brand_id = $brandId;
                $product->category_id = $categoryId;
                $product->fragrance_concentration_id = $concentrationId;
                $product->gender = $data['gender'] ?? 'Unisex';
                $product->top_notes = $data['top_notes'] ?? null;
                $product->middle_notes = $data['middle_notes'] ?? null;
                $product->base_notes = $data['base_notes'] ?? null;
                $product->key_features = $data['key_features'] ?? null;
                $product->description = $data['description'] ?? null;
                
                if (!empty($data['featured_image'])) {
                    $product->featured_image = $data['featured_image'];
                }

                if (isset($data['is_featured'])) {
                    $product->is_featured = in_array(strtolower($data['is_featured']), ['1', 'yes', 'true']);
                }

                if (isset($data['is_active'])) {
                    $product->is_active = in_array(strtolower($data['is_active']), ['1', 'yes', 'true']);
                }

                $product->save();

                // 5. Attach Fragrance Families (comma-separated)
                $familyStr = $data['fragrance_family'] ?? $data['fragrance_families'] ?? '';
                if (!empty($familyStr)) {
                    $familyNames = array_map('trim', explode(',', $familyStr));
                    $familyIds = [];
                    foreach ($familyNames as $fName) {
                        if (empty($fName)) continue;
                        $family = FragranceFamily::firstOrCreate(
                            ['name' => $fName],
                            ['slug' => Str::slug($fName)]
                        );
                        $familyIds[] = $family->id;
                    }
                    if (!empty($familyIds)) {
                        $product->fragranceFamilies()->sync($familyIds);
                    }
                }

                // 6. Create or Update Product Variant
                $size = $data['size'] ?? $data['variant_size'] ?? '100';
                $unit = $data['unit'] ?? $data['variant_unit'] ?? 'ml';
                $sellingPrice = (float) ($data['selling_price'] ?? $data['price'] ?? 0.0);
                $buyingPrice = (float) ($data['buying_price'] ?? 0.0);
                $stock = (int) ($data['stock'] ?? $data['quantity'] ?? 10);
                $variantSku = $data['variant_sku'] ?? ($sku . '-' . $size . $unit);

                $variant = ProductVariant::where('product_id', $product->id)
                    ->where(function ($q) use ($size, $variantSku) {
                        $q->where('size', $size)->orWhere('sku', $variantSku);
                    })
                    ->first();

                if (!$variant) {
                    $variant = new ProductVariant();
                    $variant->product_id = $product->id;
                }

                $variant->sku = $variantSku;
                $variant->size = $size;
                $variant->unit = $unit;
                $variant->selling_price = $sellingPrice;
                $variant->buying_price = $buyingPrice > 0 ? $buyingPrice : ($sellingPrice * 0.7);
                $variant->stock = $stock;
                $variant->save();

                if ($isNew) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum} ({$name}): " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'errors' => $errors,
        ];
    }

    public static function getSampleCsvContent(): string
    {
        $headers = [
            'sku',
            'name',
            'brand',
            'category',
            'gender',
            'fragrance_family',
            'fragrance_concentration',
            'size',
            'unit',
            'selling_price',
            'buying_price',
            'stock',
            'top_notes',
            'middle_notes',
            'base_notes',
            'key_features',
            'description',
            'featured_image',
            'is_featured',
            'is_active'
        ];

        $rows = [
            [
                'CREED-AV-100',
                'Creed Aventus Eau de Parfum',
                'Creed',
                'Niche Fragrances',
                'Unisex',
                'Woody, Amber, Citrus',
                'Eau de Parfum',
                '100',
                'ml',
                '1250.00',
                '850.00',
                '50',
                'Pineapple, Bergamot, Blackcurrant',
                'Birch, Patchouli, Moroccan Jasmine',
                'Musk, Oakmoss, Ambergris, Vanilla',
                'Royal sillage, Handcrafted in France',
                'The iconic fragrance celebrating strength, power, and success.',
                'products/aventus.png',
                '1',
                '1'
            ],
            [
                'DIOR-SAV-EX-60',
                'Dior Sauvage Elixir',
                'Dior',
                'Designer Fragrances',
                'Men',
                'Spicy, Woody',
                'Extrait de Parfum',
                '60',
                'ml',
                '780.00',
                '520.00',
                '35',
                'Nutmeg, Cinnamon, Cardamom, Grapefruit',
                'Lavender',
                'Licorice, Sandalwood, Amber, Patchouli, Vetiver',
                'Extraordinary concentration, Intense sillage',
                'An extraordinarily concentrated fragrance steeped in the iconic freshness of Sauvage.',
                'products/sauvage-elixir.png',
                '1',
                '1'
            ]
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
