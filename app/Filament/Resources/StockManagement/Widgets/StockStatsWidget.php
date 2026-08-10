<?php

namespace App\Filament\Resources\StockManagement\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;

class StockStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalInStock = Product::whereHas('variants', fn($q) => $q->where('stock', '>', 0))->count();
        $totalInOrder = ProductVariant::sum('stock_in_order');
        $totalExpired = ProductVariant::whereNotNull('expiry_date')->where('expiry_date', '<', Carbon::today())->count();

        return [
            Stat::make('Total In Stock Products', $totalInStock),
            Stat::make('Total In Order Stocks', $totalInOrder),
            Stat::make('Total Expired Products', $totalExpired),
        ];
    }
}
