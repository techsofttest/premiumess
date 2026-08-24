<?php

namespace App\Filament\Resources\ContactSettings\Pages;

use App\Filament\Resources\ContactSettings\ContactSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListContactSettings extends ListRecords
{
    protected static string $resource = ContactSettingResource::class;
}
