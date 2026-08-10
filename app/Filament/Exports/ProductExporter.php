<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('sku'),
            ExportColumn::make('stock_on_hand_total')
                ->label('Stock In Hand')
                ->getStateUsing(fn ($record) => $record->variants->sum('stock')),
            ExportColumn::make('stock_in_order_total')
                ->label('Stock In Order')
                ->getStateUsing(fn ($record) => $record->variants->sum('stock_in_order')),
            ExportColumn::make('variants.expiry_date')
                ->label('Expiry Date')
                ->getStateUsing(fn ($record) => $record->variants->first()?->expiry_date),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
