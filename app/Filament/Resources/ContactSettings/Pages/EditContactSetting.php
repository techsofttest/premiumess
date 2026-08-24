<?php

namespace App\Filament\Resources\ContactSettings\Pages;

use App\Filament\Resources\ContactSettings\ContactSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditContactSetting extends EditRecord
{
    protected static string $resource = ContactSettingResource::class;
}
