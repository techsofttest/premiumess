<?php

namespace App\Filament\Resources\ShippingSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShippingSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('default_shipping_fee')
                    ->label('Standard Shipping Charge (AED)')
                    ->helperText('Standard shipping fee applied when order subtotal is below the free shipping threshold.')
                    ->numeric()
                    ->prefix('AED')
                    ->required()
                    ->default(20.00),

                TextInput::make('free_shipping_threshold')
                    ->label('Free Shipping Limit Amount (AED)')
                    ->helperText('Orders with subtotal equal to or above this limit will get FREE shipping.')
                    ->numeric()
                    ->prefix('AED')
                    ->required()
                    ->default(200.00),

                Toggle::make('is_enabled')
                    ->label('Enable Shipping Calculation')
                    ->helperText('If disabled, all orders receive free shipping automatically.')
                    ->default(true),
            ]);
    }
}
