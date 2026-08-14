<?php

namespace App\Filament\Resources\CuratedDeals\Pages;

use App\Filament\Resources\CuratedDeals\CuratedDealResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCuratedDeal extends CreateRecord
{
    protected static string $resource = CuratedDealResource::class;
}
