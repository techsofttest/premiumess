<?php
namespace App\Filament\Resources\FragranceConcentrations\Pages;
use App\Filament\Resources\FragranceConcentrations\FragranceConcentrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListFragranceConcentrations extends ListRecords { protected static string $resource = FragranceConcentrationResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }
