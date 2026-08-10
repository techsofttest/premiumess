<?php

namespace App\Filament\Resources\StockManagement;

use App\Filament\Resources\StockManagement\Pages\ListStockManagement;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use UnitEnum;

class StockManagementResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Stock Management';

    protected static ?string $pluralLabel = 'Stock Management';

    protected static string|UnitEnum|null $navigationGroup = 'Ecommerce';

    protected static ?int $navigationSort = 10;

     public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public static function canViewAny(): bool
    {
        return auth()->user()?->can('products.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['variants' => function ($query) {
                        $query->orderBy('expiry_date', 'asc')->orderBy('id', 'asc');
                    }])
                    ->withSum('variants as stock_on_hand_total', 'stock')
                    ->withSum('variants as stock_in_order_total', 'stock_in_order')
                    ->withMin('variants as earliest_expiry_date', 'expiry_date')
                    ->orderBy('stock_in_order_total', 'desc')
                    ->orderByRaw('CASE WHEN earliest_expiry_date IS NULL THEN 1 ELSE 0 END ASC')
                    ->orderByRaw('CASE WHEN earliest_expiry_date < CURDATE() THEN 0 ELSE 1 END ASC')
                    ->orderBy('earliest_expiry_date', 'asc')
            )
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('Image')
                    ->disk('public')
                    ->size(50)
                    ->square()
                    ->visibility('public'),

                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('stock_on_hand_total')
                    ->label('Stock In Hand')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->stock_on_hand_total ?? 0)
                    ->badge()
                    ->color(function ($record) {
                        $stock = $record->stock_on_hand_total ?? 0;
                        if ($stock === 0) return 'danger';
                        if ($stock < 5) return 'danger';
                        if ($stock < 20) return 'warning';
                        return 'success';
                    })
                    ->formatStateUsing(function ($record) {
                        $stock = $record->stock_on_hand_total ?? 0;
                        if ($stock === 0) return 'Out of Stock';
                        return $stock;
                    }),

                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->getStateUsing(fn ($record) => $record->earliest_expiry_date)
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';
                        $days = \Carbon\Carbon::today()->startOfDay()->diffInDays(\Carbon\Carbon::parse($state)->startOfDay(), false);
                        if ($days < 0) return 'Expired';
                        if ($days == 0) return 'Today';
                        if ($days == 1) return 'Tomorrow';
                        return $days . ' days';
                    }),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockManagement::route('/'),
        ];
    }
}
