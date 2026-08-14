<?php

namespace App\Filament\Resources\CuratedDeals\Pages;

use App\Filament\Resources\CuratedDeals\CuratedDealResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCuratedDeals extends ListRecords
{
    protected static string $resource = CuratedDealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
