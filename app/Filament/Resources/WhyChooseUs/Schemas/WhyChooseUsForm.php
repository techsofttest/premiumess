<?php

namespace App\Filament\Resources\WhyChooseUs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WhyChooseUsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('title')
                    ->label('Feature Title (e.g. 100% Authentic)')
                    ->required(),
                Textarea::make('description')
                    ->label('Feature Description')
                    ->required()
                    ->rows(3),
                Select::make('icon')
                    ->label('Feature Icon')
                    ->options([
                        'ShieldCheck' => 'Shield / Authenticity (ShieldCheck)',
                        'Crown' => 'Crown / Prestigious (Crown)',
                        'Sparkles' => 'Sparkles / Premium Service (Sparkles)',
                        'Droplet' => 'Perfume Droplet / Excellence (Droplet)',
                        'Award' => 'Award Badge (Award)',
                        'Truck' => 'Express Delivery (Truck)',
                        'Heart' => 'Customer Care (Heart)',
                        'Star' => 'Star Rating (Star)',
                    ])
                    ->default('ShieldCheck')
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Display Order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
