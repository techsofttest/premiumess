<?php

namespace App\Filament\Resources\StaffUsers\Pages;

use App\Filament\Resources\StaffUsers\StaffUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffUser extends CreateRecord
{
    protected static string $resource = StaffUserResource::class;

    protected function afterCreate(): void
    {
        $this->record->assignRole('Staff');
        $this->record->update(['role' => 'staff']);
    }
}
