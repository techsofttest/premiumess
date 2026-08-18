<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Banner Name / Title')
                    ->required(),
                Select::make('position')
                    ->label('Banner Location / Position')
                    ->options([
                        'hero' => 'Top Hero Slider (Home)',
                        'middle' => 'Middle Product Banner (Between Featured & Curated Collection)',
                    ])
                    ->default('hero')
                    ->required(),
                FileUpload::make('image')
                    ->label('Banner Image')
                    ->disk('public')
                    ->directory('banners')
                    ->image()
                    ->required(),
                TextInput::make('url')
                    ->label('Click Target Link URL (e.g. /shop or /deals)')
                    ->placeholder('/shop'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
