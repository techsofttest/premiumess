<?php
namespace App\Filament\Resources\FragranceFamilies\Pages;
use App\Filament\Resources\FragranceFamilies\FragranceFamilyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditFragranceFamily extends EditRecord { protected static string $resource = FragranceFamilyResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }
