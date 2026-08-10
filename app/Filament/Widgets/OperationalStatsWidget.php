<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Enums\OrderStatus;
use Carbon\Carbon;

class OperationalStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        $pendingCount = Order::whereIn('status', [
            OrderStatus::PENDING_PAYMENT,
            OrderStatus::CONFIRMED
        ])->count();

        $processingCount = Order::whereIn('status', [
            OrderStatus::PROCESSING,
            OrderStatus::PACKED
        ])->count();

        $readyCount = Order::where('status', OrderStatus::READY)->count();

        $deliveredToday = Order::where('status', OrderStatus::DELIVERED)
            ->whereDate('updated_at', Carbon::today())
            ->count();

        $failedOrCancelled = Order::whereIn('status', [
            OrderStatus::CANCELLED,
            OrderStatus::REFUNDED
        ])->count();

        return [
            Stat::make('Pending Orders', $pendingCount)
                ->description('Awaiting action/payment')
                ->color('warning'),
                
            Stat::make('Processing / Packed', $processingCount)
                ->description('In progress')
                ->color('info'),
                
            Stat::make('Ready for Dispatch', $readyCount)
                ->description('Prepared to ship')
                ->color('primary'),

            Stat::make('Delivered Today', $deliveredToday)
                ->description('Completed deliveries')
                ->color('success'),

            Stat::make('Failed / Cancelled', $failedOrCancelled)
                ->description('Cancelled or refunded')
                ->color('danger'),
        ];
    }
}
