<?php

namespace App\Filament\Resources\CuratedDeals;

use App\Filament\Resources\CuratedDeals\Pages\CreateCuratedDeal;
use App\Filament\Resources\CuratedDeals\Pages\EditCuratedDeal;
use App\Filament\Resources\CuratedDeals\Pages\ListCuratedDeals;
use App\Filament\Resources\CuratedDeals\Schemas\CuratedDealForm;
use App\Filament\Resources\CuratedDeals\Tables\CuratedDealsTable;
use App\Models\CuratedDeal;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CuratedDealResource extends Resource
{
    protected static ?string $model = CuratedDeal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Ecommerce';

    protected static ?string $navigationLabel = 'Special Deals / Bundles';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return CuratedDealForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CuratedDealsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCuratedDeals::route('/'),
            'create' => CreateCuratedDeal::route('/create'),
            'edit' => EditCuratedDeal::route('/{record}/edit'),
        ];
    }
}
