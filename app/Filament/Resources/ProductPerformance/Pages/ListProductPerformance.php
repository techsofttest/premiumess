<?php

namespace App\Filament\Resources\ProductPerformance\Pages;

use App\Filament\Resources\ProductPerformance\ProductPerformanceResource;
use Filament\Resources\Pages\ListRecords;

class ListProductPerformance extends ListRecords
{
    protected static string $resource = ProductPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
        
    }
}
