<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\CmsResource;
use Filament\Resources\Pages\ListRecords;

class ListCms extends ListRecords
{
    protected static string $resource = CmsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
