<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('coupon_code')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('coupon_name')
                    ->label('Coupon Name')
                    ->maxLength(255),

                Radio::make('coupon_type')
                    ->label('Coupon Type')
                    ->options([
                        0 => 'Cash',
                        1 => 'Percentage',
                    ])
                    ->required()
                    ->columnSpanFull()
                    ->inline(),

                TextInput::make('coupon_amount')
                    ->required()
                    ->numeric(),

                DatePicker::make('coupon_fromdate')
                    ->label('Start Date'),

                DatePicker::make('coupon_todate')
                    ->label('End Date'),

                TextInput::make('minimum_order_amount')
                    ->label('Minimum Order Amount')
                    ->numeric()
                    ->default(0.00),

                TextInput::make('maximum_discount')
                    ->label('Maximum Discount (Percentage only)')
                    ->numeric(),

                TextInput::make('global_usage_limit')
                    ->label('Global Usage Limit')
                    ->integer()
                    ->default(0)
                    ->helperText('0 = unlimited'),

                TextInput::make('customer_usage_limit')
                    ->label('Per Customer Limit')
                    ->integer()
                    ->default(0)
                    ->helperText('0 = unlimited'),

                /*Toggle::make('first_order_only')
                    ->label('First Order Only')
                    ->default(false),*/

                Toggle::make('active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
