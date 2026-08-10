<?php

namespace App\Filament\Resources\StaffUsers;

use App\Filament\Resources\StaffUsers\Pages\CreateStaffUser;
use App\Filament\Resources\StaffUsers\Pages\EditStaffUser;
use App\Filament\Resources\StaffUsers\Pages\ListStaffUsers;
use App\Filament\Resources\StaffUsers\Schemas\StaffUserForm;
use App\Filament\Resources\StaffUsers\Tables\StaffUsersTable;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StaffUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Staff Users';

    protected static ?string $modelLabel = 'Staff User';

    protected static ?string $pluralModelLabel = 'Staff Users';

    protected static ?string $slug = 'staff-users';

    protected static string|UnitEnum|null $navigationGroup = 'Staff Management';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('staff-users.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('staff-users.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('staff-users.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Prevent deleting yourself
        if ($user->id === $record->id) {
            return false;
        }

        return $user->can('staff-users.delete');
    }

    public static function form(Schema $schema): Schema
    {
        return StaffUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffUsersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role('Staff');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffUsers::route('/'),
            'create' => CreateStaffUser::route('/create'),
            'edit' => EditStaffUser::route('/{record}/edit'),
        ];
    }
}
