<?php

namespace App\Filament\Resources\WhyChooseUs\Pages;

use App\Filament\Resources\WhyChooseUs\WhyChooseUsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWhyChooseUs extends EditRecord
{
    protected static string $resource = WhyChooseUsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
