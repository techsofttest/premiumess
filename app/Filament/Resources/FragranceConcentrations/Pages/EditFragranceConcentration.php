<?php
namespace App\Filament\Resources\FragranceConcentrations\Pages;
use App\Filament\Resources\FragranceConcentrations\FragranceConcentrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditFragranceConcentration extends EditRecord { protected static string $resource = FragranceConcentrationResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }
