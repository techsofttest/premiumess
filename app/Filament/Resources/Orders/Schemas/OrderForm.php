<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Enums\OrderStatus;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        $isStaff = auth()->user()?->hasRole('Staff') ?? false;

        return $schema
            ->components([
                Section::make('Order Summary')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('order_number')
                                ->required()
                                ->disabled($isStaff),
                            Select::make('status')
                                ->options([
                                    OrderStatus::PENDING_PAYMENT->value => 'Pending Payment',
                                    OrderStatus::CONFIRMED->value => 'Confirmed',
                                    OrderStatus::PROCESSING->value => 'Processing',
                                    OrderStatus::PACKED->value => 'Packed',
                                    OrderStatus::READY->value => 'Ready for Dispatch',
                                    OrderStatus::OUT_FOR_DELIVERY->value => 'Out for Delivery',
                                    OrderStatus::DELIVERED->value => 'Delivered',
                                    OrderStatus::CANCELLED->value => 'Cancelled',
                                    OrderStatus::REFUND_REQUESTED->value => 'Refund Requested',
                                    OrderStatus::REFUNDED->value => 'Refunded',
                                ])
                                ->required(),
                            Select::make('payment_status')
                                ->options([
                                    'pending' => 'Pending',
                                    'paid' => 'Paid',
                                    'failed' => 'Failed',
                                    'refunded' => 'Refunded',
                                    'partially_refunded' => 'Partially Refunded',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required()
                                ->default('pending')
                                ->disabled($isStaff),
                            TextInput::make('payment_method')
                                ->required()
                                ->disabled($isStaff),
                        ]),
                    ]),

                Section::make('Customer')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('customer_id')
                                ->numeric()
                                ->disabled(),
                            TextInput::make('customer_name')
                                ->required()
                                ->disabled($isStaff),
                            TextInput::make('customer_email')
                                ->email()
                                ->disabled($isStaff),
                            TextInput::make('customer_phone')
                                ->tel()
                                ->disabled($isStaff),
                        ]),
                    ]),

                Section::make('Shipping Snapshot')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('shipping_name')->disabled($isStaff),
                            TextInput::make('shipping_phone')->disabled($isStaff),
                            TextInput::make('shipping_address_line_1')->disabled($isStaff),
                            TextInput::make('shipping_address_line_2')->disabled($isStaff),
                            TextInput::make('shipping_suburb')->disabled($isStaff),
                            TextInput::make('shipping_city')->disabled($isStaff),
                            TextInput::make('shipping_state')->disabled($isStaff),
                            TextInput::make('shipping_postcode')->disabled($isStaff),
                            TextInput::make('shipping_country')->disabled($isStaff),
                            TextInput::make('shipping_latitude')->disabled($isStaff),
                            TextInput::make('shipping_longitude')->disabled($isStaff),
                            TextInput::make('shipping_google_place_id')->disabled($isStaff),
                            TextInput::make('delivery_type')->disabled($isStaff),
                            TextInput::make('warehouse_id')->disabled($isStaff),
                            TextInput::make('delivery_slot_id')->disabled($isStaff),
                            TextInput::make('delivery_date')->disabled($isStaff),
                            TextInput::make('delivery_distance_km')->disabled($isStaff),
                        ]),
                    ]),

                Section::make('Totals')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('subtotal')->numeric()->default(0.0)->disabled($isStaff),
                            TextInput::make('shipping_cost')->numeric()->default(0.0)->prefix('$')->disabled($isStaff),
                            TextInput::make('discount')->numeric()->default(0.0)->disabled($isStaff),
                            TextInput::make('coupon_code')->disabled($isStaff),
                            TextInput::make('grand_total')->numeric()->default(0.0)->disabled($isStaff),
                        ]),
                        Textarea::make('notes')->columnSpanFull()->disabled($isStaff),
                    ]),
            ]);
    }
}

