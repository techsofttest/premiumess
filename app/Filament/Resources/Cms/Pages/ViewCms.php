<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\CmsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCms extends ViewRecord
{
    protected static string $resource = CmsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
