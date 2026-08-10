<?php
namespace App\Filament\Resources\FragranceFamilies\Pages;
use App\Filament\Resources\FragranceFamilies\FragranceFamilyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListFragranceFamilies extends ListRecords { protected static string $resource = FragranceFamilyResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }
