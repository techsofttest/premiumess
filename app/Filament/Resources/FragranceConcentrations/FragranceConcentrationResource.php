<?php

namespace App\Filament\Resources\FragranceConcentrations;

use App\Filament\Resources\FragranceConcentrations\Pages\CreateFragranceConcentration;
use App\Filament\Resources\FragranceConcentrations\Pages\EditFragranceConcentration;
use App\Filament\Resources\FragranceConcentrations\Pages\ListFragranceConcentrations;
use App\Models\FragranceConcentration;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class FragranceConcentrationResource extends Resource
{
    protected static ?string $model = FragranceConcentration::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'Master data';
    protected static ?string $navigationLabel = 'Fragrance Concentrations';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->hidden()->dehydrated(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('slug')->searchable(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->recordActions([\Filament\Actions\EditAction::make()])
            ->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListFragranceConcentrations::route('/'), 'create' => CreateFragranceConcentration::route('/create'), 'edit' => EditFragranceConcentration::route('/{record}/edit')];
    }
}
