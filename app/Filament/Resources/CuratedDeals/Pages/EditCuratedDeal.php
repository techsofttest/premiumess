<?php

namespace App\Filament\Resources\CuratedDeals\Pages;

use App\Filament\Resources\CuratedDeals\CuratedDealResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCuratedDeal extends EditRecord
{
    protected static string $resource = CuratedDealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
