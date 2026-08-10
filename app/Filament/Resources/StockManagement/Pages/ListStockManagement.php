<?php

namespace App\Filament\Resources\StockManagement\Pages;

use App\Filament\Resources\StockManagement\StockManagementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\ExportAction;
use App\Filament\Exports\ProductExporter;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Category;

class ListStockManagement extends ListRecords
{
    protected static string $resource = StockManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(ProductExporter::class)
                ->label('Export to Excel')
                ->color('success'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\StockManagement\Widgets\StockStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All Products'),
        ];

        $categories = Category::whereNull('parent_id')->get(); // Main categories
        foreach ($categories as $category) {
            $tabs[$category->slug ?? $category->id] = Tab::make($category->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category_id', $category->id));
        }

        return $tabs;
    }
}
