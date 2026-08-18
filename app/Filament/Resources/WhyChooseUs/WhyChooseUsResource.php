<?php

namespace App\Filament\Resources\WhyChooseUs;

use App\Filament\Resources\WhyChooseUs\Pages\CreateWhyChooseUs;
use App\Filament\Resources\WhyChooseUs\Pages\EditWhyChooseUs;
use App\Filament\Resources\WhyChooseUs\Pages\ListWhyChooseUs;
use App\Filament\Resources\WhyChooseUs\Schemas\WhyChooseUsForm;
use App\Filament\Resources\WhyChooseUs\Tables\WhyChooseUsTable;
use App\Models\WhyChooseUsItem;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhyChooseUsResource extends Resource
{
    protected static ?string $model = WhyChooseUsItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Our Values (Why Choose Us)';

    protected static ?string $pluralModelLabel = 'Our Values / Why Choose Us';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return WhyChooseUsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhyChooseUsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhyChooseUs::route('/'),
            'create' => CreateWhyChooseUs::route('/create'),
            'edit' => EditWhyChooseUs::route('/{record}/edit'),
        ];
    }
}
