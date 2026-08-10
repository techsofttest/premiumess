<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Product;
use Carbon\Carbon;

class ExpiringProductsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    
    protected static ?int $sort = 2;
    
    protected static ?string $heading = 'Products Expiring Soon';

    public static function canView(): bool
    {
        return auth()->user()?->can('products.view') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereHas('variants', function ($query) {
                        $query->whereNotNull('expiry_date')
                            ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(10)]);
                    })
                    ->with(['variants' => function ($query) {
                        $query->whereNotNull('expiry_date')
                            ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(10)])
                            ->orderBy('expiry_date', 'asc');
                    }])
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name'),
                Tables\Columns\TextColumn::make('variants')
                    ->label('Expiring in')
                    ->getStateUsing(function ($record) {
                        $variant = $record->variants->first();
                        return $variant ? $variant->expiry_date : null;
                    })
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';
                        $days = Carbon::today()->startOfDay()->diffInDays(Carbon::parse($state)->startOfDay());
                        if ($days == 0) return 'Today';
                        if ($days == 1) return 'Tomorrow';
                        return $days . ' days';
                    }),
            ])
            ->paginated(false);
    }
}
