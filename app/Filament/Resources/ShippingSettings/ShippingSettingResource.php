<?php

namespace App\Filament\Resources\ShippingSettings;

use App\Filament\Resources\ShippingSettings\Pages\EditShippingSetting;
use App\Filament\Resources\ShippingSettings\Pages\ListShippingSettings;
use App\Filament\Resources\ShippingSettings\Schemas\ShippingSettingForm;
use App\Filament\Resources\ShippingSettings\Tables\ShippingSettingsTable;
use App\Models\ShippingSetting;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShippingSettingResource extends Resource
{
    protected static ?string $model = ShippingSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Shipping Settings';

    protected static ?string $pluralModelLabel = 'Shipping Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Fulfillment';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ShippingSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingSettingsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return ShippingSetting::count() === 0;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShippingSettings::route('/'),
            'edit' => EditShippingSetting::route('/{record}/edit'),
        ];
    }
}
