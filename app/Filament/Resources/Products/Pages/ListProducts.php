<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('download_sample')
                ->label('Download Sample Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $csv = \App\Services\ProductCsvImporter::getSampleCsvContent();
                    return response()->streamDownload(function () use ($csv) {
                        echo $csv;
                    }, 'sample_products_import_template.csv', [
                        'Content-Type' => 'text/csv',
                    ]);
                }),

            \Filament\Actions\Action::make('import_excel')
                ->label('Import Products (Excel/CSV)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Products via Excel / CSV')
                ->modalDescription('Upload your CSV or Excel file containing product data. Download the sample template above if you need a reference for required columns and formatting.')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('attachment')
                        ->label('Product Data File (CSV / Excel)')
                        ->required()
                        ->disk('public')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]),
                ])
                ->action(function (array $data) {
                    $filePath = storage_path('app/public/' . $data['attachment']);
                    try {
                        $result = \App\Services\ProductCsvImporter::import($filePath);
                        
                        $created = $result['created'];
                        $updated = $result['updated'];
                        $errCount = count($result['errors']);

                        $body = "Import Finished! Created: {$created} new products | Updated: {$updated} existing products.";
                        if ($errCount > 0) {
                            $body .= " ({$errCount} row issues encountered)";
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Product Import Complete')
                            ->body($body)
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Import Failed')
                            ->body('Failed to parse import file: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                    }
                }),

            CreateAction::make(),
        ];
    }
}
