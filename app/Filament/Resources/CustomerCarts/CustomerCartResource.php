<?php

namespace App\Filament\Resources\CustomerCarts;

use App\Filament\Resources\CustomerCarts\Pages\ListCustomerCarts;
use App\Models\CustomerCart;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerCartResource extends Resource
{
    protected static ?string $model = CustomerCart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Ecommerce';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Customer Carts';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->default('Guest / Anonymous'),

                TextColumn::make('customer.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Unique Products')
                    ->getStateUsing(fn ($record) => $record->items()->count())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),

                TextColumn::make('total_quantity')
                    ->label('Total Quantity')
                    ->getStateUsing(fn ($record) => $record->items()->sum('quantity')),

                TextColumn::make('cart_products')
                    ->label('Products in Cart')
                    ->getStateUsing(function ($record) {
                        $items = $record->items()->with(['product', 'variant'])->get();
                        if ($items->isEmpty()) {
                            return 'Empty Cart';
                        }

                        return $items->map(function ($item) {
                            $size = trim(($item->variant->size ?? '') . ($item->variant->unit ? ' ' . $item->variant->unit : ''));
                            return ($item->product->name ?? 'Product') . ($size ? " ({$size})" : '') . " × {$item->quantity}";
                        })->implode(', ');
                    })
                    ->wrap()
                    ->limit(100),

                TextColumn::make('total_value')
                    ->label('Total Cart Value')
                    ->getStateUsing(function ($record) {
                        $items = $record->items()->with(['variant'])->get();
                        $sum = $items->sum(fn ($item) => (float) ($item->variant->selling_price ?? 0) * $item->quantity);
                        return number_format($sum, 2) . ' AED';
                    })
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view_cart_items')
                    ->label('View Cart')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('warning')
                    ->modalHeading(fn ($record) => "Cart Products for " . ($record->customer->name ?? 'Customer'))
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(function ($record) {
                        $items = $record->items()->with(['product.brand', 'variant'])->get();
                        return view('filament.components.customer-cart-modal', [
                            'customer' => $record->customer,
                            'items' => $items,
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerCarts::route('/'),
        ];
    }
}
