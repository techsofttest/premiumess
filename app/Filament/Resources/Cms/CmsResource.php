<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Resources\Cms\Pages\EditCms;
use App\Filament\Resources\Cms\Pages\ListCms;
use App\Filament\Resources\Cms\Pages\ViewCms;
use App\Filament\Resources\Cms\Schemas\CmsForm;
use App\Filament\Resources\Cms\Schemas\CmsInfolist;
use App\Filament\Resources\Cms\Tables\CmsTable;
use App\Models\Cms;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CmsResource extends Resource
{
    protected static ?string $model = Cms::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CmsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CmsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CmsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
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
            'index' => ListCms::route('/'),
            'view' => ViewCms::route('/{record}'),
            'edit' => EditCms::route('/{record}/edit'),
        ];
    }
}
