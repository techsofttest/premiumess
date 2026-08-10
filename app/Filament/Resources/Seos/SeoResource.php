<?php

namespace App\Filament\Resources\Seos;

use App\Filament\Resources\Seos\Pages\CreateSeo;
use App\Filament\Resources\Seos\Pages\EditSeo;
use App\Filament\Resources\Seos\Pages\ListSeos;
use App\Filament\Resources\Seos\Schemas\SeoForm;
use App\Filament\Resources\Seos\Tables\SeosTable;
use App\Models\Seo;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeoResource extends Resource
{
    protected static ?string $model = Seo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return SeoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeos::route('/'),
            'create' => CreateSeo::route('/create'),
            'edit' => EditSeo::route('/{record}/edit'),
        ];
    }
}
