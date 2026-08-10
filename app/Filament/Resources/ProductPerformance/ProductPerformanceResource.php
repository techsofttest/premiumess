<?php

namespace App\Filament\Resources\ProductPerformance;

use App\Models\Product;
use BackedEnum;
use UnitEnum;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductPerformance\Pages\ListProductPerformance;

class ProductPerformanceResource extends Resource   
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $modelLabel = 'Product Performance';
    protected static ?string $pluralModelLabel = 'Product Performance';
    protected static ?string $navigationLabel = 'Performance';

    protected static string|UnitEnum|null $navigationGroup = 'Ecommerce';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return Product::query()
            ->with(['variants'])
            ->withCount([
                'orderItems as period_order_count' => function (Builder $query) {
                    // Default to 1 month; overridden by filter via scope
                    $query->whereHas('order', fn (Builder $q) => $q->where('created_at', '>=', Carbon::now()->subMonth()));
                },
            ])
            ->withMax('orderItems as last_order_date', 'created_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('period_order_count', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('Product SKU')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('variants_skus')
                    ->label('Child SKUs')
                    ->getStateUsing(function ($record) {
                        return $record->variants->pluck('sku')->filter()->join(', ') ?: '—';
                    })
                    ->wrap(),

                TextColumn::make('last_order_date')
                    ->label('Last Order Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('No orders yet'),

                TextColumn::make('period_order_count')
                    ->label('Orders in Period')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 50  => 'success',
                        $state >= 10  => 'warning',
                        $state >= 1   => 'info',
                        default       => 'danger',
                    }),
            ])
            ->filters([
                Filter::make('period') 
                    ->form([
                        Select::make('period')
                            ->label('Time Period')
                            ->options([
                                '1week'   => 'Last 1 Week',
                                '1month'  => 'Last 1 Month',
                                '3months' => 'Last 3 Months',
                            ])
                            ->default('1month'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $since = match ($data['period'] ?? '1month') {
                            '1week'   => Carbon::now()->subWeek(),
                            '3months' => Carbon::now()->subMonths(3),
                            default   => Carbon::now()->subMonth(),
                        };

                        $query->withCount([
                            'orderItems as period_order_count' => function (Builder $q) use ($since) {
                                $q->whereHas('order', fn (Builder $oq) => $oq->where('created_at', '>=', $since));
                            },
                        ]);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return match ($data['period'] ?? null) {
                            '1week'   => 'Period: Last 1 Week',
                            '1month'  => 'Period: Last 1 Month',
                            '3months' => 'Period: Last 3 Months',
                            default   => null,
                        };
                    }),

                Filter::make('performance')
                    ->form([

                        Select::make('sort_by')
                            ->label('Sort by Performance')
                            ->options([
                                'high' => 'High Performing (Most Orders First)',
                                'low'  => 'Low Performing (Fewest Orders First)',
                            ])
                            ->default('high'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $dir = ($data['sort_by'] ?? 'high') === 'low' ? 'asc' : 'desc';
                        $query->orderBy('period_order_count', $dir);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return match ($data['sort_by'] ?? null) {
                            'high' => 'Sorted: High Performing',
                            'low'  => 'Sorted: Low Performing',
                            default => null,
                        };
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductPerformance::route('/'),
        ];
    }
}
