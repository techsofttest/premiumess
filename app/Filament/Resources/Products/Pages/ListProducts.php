<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use App\Models\Category;
use Filament\Schemas\Components\Tabs\Tab;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('All Products')];

        $categories = Category::query()->with('children')->get();

        foreach ($categories as $category) {
            $categoryIds = $this->getCategoryIds($category);

            $tabs[$category->slug] = Tab::make($category->name)
                ->modifyQueryUsing(fn($query) => $query->whereIn('category_id', $categoryIds));
        }

        return $tabs;
    }

    protected function getCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getCategoryIds($child));
        }

        return array_values(array_unique($ids));
    }

    protected function getHeaderActions(): array
    {
        return [
            /*\Filament\Actions\Action::make('import_images')
                ->label('Import Images (ZIP)')
                ->icon('heroicon-o-photo')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('zip_file')
                        ->label('ZIP Archive of Images')
                        ->required()
                        ->disk('public')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed']),
                ])
                ->action(function (array $data) {
                    $zipPath = storage_path('app/public/' . $data['zip_file']);
                    $zip = new \ZipArchive;
                    if ($zip->open($zipPath) === TRUE) {
                        $extractedCount = 0;
                        $tempDir = storage_path('app/temp_zip_' . uniqid());
                        mkdir($tempDir);
                        $zip->extractTo($tempDir);
                        $zip->close();

                        $files = scandir($tempDir);
                        foreach ($files as $file) {
                            if (in_array($file, ['.', '..'])) continue;
                            
                            $filePath = $tempDir . DIRECTORY_SEPARATOR . $file;
                            if (is_dir($filePath)) continue;

                            $filename = pathinfo($file, PATHINFO_FILENAME);
                            
                            // Match logic: Priority 1 - Exact slug match
                            $product = \App\Models\Product::where('prod_slug', $filename)->first();
                            
                            // Priority 2 - Check if this filename is already registered in prod_image
                            if (!$product) {
                                $product = \App\Models\Product::where('prod_image', 'like', '%' . $file)->first();
                            }

                            if ($product) {
                                $newName = 'products/' . $file;
                                $destPath = storage_path('app/public/' . $newName);
                                if (!is_dir(dirname($destPath))) {
                                    mkdir(dirname($destPath), 0755, true);
                                }
                                copy($filePath, $destPath);
                                $product->update(['prod_image' => $newName]);
                                $extractedCount++;
                            }
                        }

                        // Cleanup temp dir
                        \Illuminate\Support\Facades\File::deleteDirectory($tempDir);
                        \Illuminate\Support\Facades\File::delete($zipPath);

                        \Filament\Notifications\Notification::make()
                            ->title('Success')
                            ->body("Processed {$extractedCount} images and matched to products.")
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body('Failed to open ZIP file.')
                            ->danger()
                            ->send();
                    }
                }),
            \Filament\Actions\Action::make('import_excel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('attachment')
                        ->label('Excel File')
                        ->required()
                        ->disk('public')
                        ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv']),
                ])
                ->action(function (array $data) {
                    $file = storage_path('app/public/' . $data['attachment']);
                    try {
                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProductsImport, $file);
                        \Filament\Notifications\Notification::make()
                            ->title('Success')
                            ->body('Products imported successfully!')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body('Failed to import products: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }), */
            CreateAction::make(),
        ];
    }
}
