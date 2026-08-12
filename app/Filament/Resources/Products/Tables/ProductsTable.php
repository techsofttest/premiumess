<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Imports\ProductImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn;


class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([

                ImageColumn::make('featured_image')
                    ->label('Image')
                    ->disk('public')
                    ->size(50)
                    ->square()
                    ->visibility('public'),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                // TextColumn::make('supplier_code')
                //     ->label('Supplier')
                //     ->searchable(),
                    

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // TextColumn::make('category.name')
                //     ->label('Category')
                //     ->sortable(),

                    // TextColumn::make('gender')
                    //     ->label('Gender')
                    //     ->badge()
                    //     ->toggleable(),

                TextColumn::make('collections.name')
                    ->label('Collections')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                // TextColumn::make('fragranceFamily.name')
                //     ->label('Family')
                //     ->sortable()
                //     ->toggleable(),

                TextColumn::make('fragranceConcentration.name')
                    ->label('Concentration')
                    ->sortable()
                    ->toggleable(),

                /*TextColumn::make('variants_count')
                    ->label('Variants')
                    ->counts('variants')
                    ->sortable(),*/

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])

            ->recordActions([
                EditAction::make(),
            ])

            // ->headerActions([
            //     \Filament\Actions\Action::make('import_products')
            //         ->label('Import Products')
            //         ->icon('heroicon-o-arrow-up-tray')
            //         ->form([
            //             \Filament\Forms\Components\FileUpload::make('file')
            //                 ->label('Excel / CSV File')
            //                 ->required()
            //                 ->disk('local')
            //                 ->directory('imports'),
            //             \Filament\Forms\Components\FileUpload::make('zip')
            //                 ->label('Images ZIP (Optional)')
            //                 ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'])
            //                 ->disk('local')
            //                 ->directory('imports'),
            //         ])
            //         ->action(function (array $data) {
            //             $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path($data['file']);
            //             $imageService = null;

            //             try {
            //                 if (!empty($data['zip'])) {
            //                     $zipPath = \Illuminate\Support\Facades\Storage::disk('local')->path($data['zip']);
            //                     $imageService = app(\App\Services\Import\ImageImportService::class);
            //                     $lookup = $imageService->extractZip($zipPath);
            //                     app(\App\Services\Import\ImageResolver::class)->setLookup($lookup);
            //                 }

            //                 $import = new \App\Imports\ProductExcelImport();
            //                 \Maatwebsite\Excel\Facades\Excel::import($import, $filePath);

            //                 $summary = $import->getService()->getLogger()->getFormattedSummary();

            //                 \Filament\Notifications\Notification::make()
            //                     ->title('Import Completed')
            //                     ->body($summary)
            //                     ->success()
            //                     ->send();
            //             } finally {
            //                 if ($imageService) {
            //                     $imageService->cleanup();
            //                 }
            //                 // Delete uploaded temp files
            //                 if (\Illuminate\Support\Facades\Storage::disk('local')->exists($data['file'])) {
            //                     \Illuminate\Support\Facades\Storage::disk('local')->delete($data['file']);
            //                 }
            //                 if (!empty($data['zip']) && \Illuminate\Support\Facades\Storage::disk('local')->exists($data['zip'])) {
            //                     \Illuminate\Support\Facades\Storage::disk('local')->delete($data['zip']);
            //                 }
            //             }
            //         })
            // ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
