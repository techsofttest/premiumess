<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use Carbon\Carbon;

class DashboardStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $ordersToday = Order::whereDate('created_at', $today)->count();
        $ordersYesterday = Order::whereDate('created_at', $yesterday)->count();
        $ordersDiff = $ordersToday - $ordersYesterday;

        $revenueToday = Order::whereDate('created_at', $today)->sum('grand_total');
        $revenueYesterday = Order::whereDate('created_at', $yesterday)->sum('grand_total');
        $revenueDiff = $revenueToday - $revenueYesterday;

        $ordersThisMonth = Order::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count();
        $ordersLastMonth = Order::whereMonth('created_at', $today->copy()->subMonth()->month)->whereYear('created_at', $today->copy()->subMonth()->year)->count();
        $monthOrdersDiff = $ordersThisMonth - $ordersLastMonth;

        return [
            Stat::make('Total Orders Today', $ordersToday)
                ->description($ordersDiff > 0 ? $ordersDiff . ' increase from yesterday' : abs($ordersDiff) . ' decrease from yesterday')
                ->descriptionIcon($ordersDiff > 0 ? 'heroicon-m-arrow-trending-up' : ($ordersDiff < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($ordersDiff > 0 ? 'success' : ($ordersDiff < 0 ? 'danger' : 'gray')),
                
            Stat::make('Total Revenue Today', '$' . number_format($revenueToday, 2))
                ->description($revenueDiff > 0 ? '$' . number_format($revenueDiff, 2) . ' increase' : '$' . number_format(abs($revenueDiff), 2) . ' decrease')
                ->descriptionIcon($revenueDiff > 0 ? 'heroicon-m-arrow-trending-up' : ($revenueDiff < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($revenueDiff > 0 ? 'success' : ($revenueDiff < 0 ? 'danger' : 'gray')),
                
            Stat::make('Total Orders This Month', $ordersThisMonth)
                ->description($monthOrdersDiff > 0 ? $monthOrdersDiff . ' increase from last month' : abs($monthOrdersDiff) . ' decrease from last month')
                ->descriptionIcon($monthOrdersDiff > 0 ? 'heroicon-m-arrow-trending-up' : ($monthOrdersDiff < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($monthOrdersDiff > 0 ? 'success' : ($monthOrdersDiff < 0 ? 'danger' : 'gray')),
        ];
    }
}
