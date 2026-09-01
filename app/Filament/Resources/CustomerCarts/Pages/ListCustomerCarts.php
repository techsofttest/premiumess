<?php

namespace App\Filament\Resources\CustomerCarts\Pages;

use App\Filament\Resources\CustomerCarts\CustomerCartResource;
use Filament\Resources\Pages\ListRecords;

class ListCustomerCarts extends ListRecords
{
    protected static string $resource = CustomerCartResource::class;
}
