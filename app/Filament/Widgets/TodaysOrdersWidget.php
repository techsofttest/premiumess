<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Order;
use Carbon\Carbon;

class TodaysOrdersWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    
    protected static ?int $sort = 3;
    
    protected static ?string $heading = 'Today\'s Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->whereDate('created_at', Carbon::today())
                    ->latest()
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order Number'),
                Tables\Columns\TextColumn::make('customer')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name)),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('AUD'),
            ])
            ->paginated(false);
    }
}
