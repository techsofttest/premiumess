<?php

namespace App\Filament\Resources\FragranceFamilies;

use App\Filament\Resources\FragranceFamilies\Pages\CreateFragranceFamily;
use App\Filament\Resources\FragranceFamilies\Pages\EditFragranceFamily;
use App\Filament\Resources\FragranceFamilies\Pages\ListFragranceFamilies;
use App\Models\FragranceFamily;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class FragranceFamilyResource extends Resource
{
    protected static ?string $model = FragranceFamily::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static string|UnitEnum|null $navigationGroup = 'Master data';
    protected static ?string $navigationLabel = 'Fragrance Families';
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
        return ['index' => ListFragranceFamilies::route('/'), 'create' => CreateFragranceFamily::route('/create'), 'edit' => EditFragranceFamily::route('/{record}/edit')];
    }
}
