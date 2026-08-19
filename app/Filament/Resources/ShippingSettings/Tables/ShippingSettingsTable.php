<?php

namespace App\Filament\Resources\ShippingSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('default_shipping_fee')
                    ->label('Standard Shipping Charge')
                    ->money('AED'),
                TextColumn::make('free_shipping_threshold')
                    ->label('Free Shipping Limit')
                    ->money('AED'),
                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
