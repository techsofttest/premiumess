<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('classification')
                    ->options([
                        'Designer Houses' => 'Designer Houses',
                        'Prestige & Niche' => 'Prestige & Niche',
                        'Classic Elegance' => 'Classic Elegance',
                    ])
                    ->nullable()
                    ->placeholder('Select Brand Classification'),
                TextInput::make('slug')
                    ->hidden(),
                FileUpload::make('logo')
                    ->disk('public')
                    ->image(),
                /*RichEditor::make('description')
                    ->columnSpanFull(),*/
            ]);
    }
}
