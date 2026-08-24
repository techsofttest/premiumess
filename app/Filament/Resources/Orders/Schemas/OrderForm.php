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

                Section::make('Billing Snapshot')
                    ->schema([
                        \Filament\Forms\Components\Toggle::make('billing_same_as_shipping')
                            ->label('Billing Address is same as Shipping Address')
                            ->disabled($isStaff)
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('billing_name')->label('Billing Contact Name')->disabled($isStaff),
                            TextInput::make('billing_phone')->label('Billing Phone')->disabled($isStaff),
                            TextInput::make('billing_address_line_1')->label('Billing Address Line 1')->disabled($isStaff),
                            TextInput::make('billing_address_line_2')->label('Billing Address Line 2')->disabled($isStaff),
                            TextInput::make('billing_city')->label('Billing City')->disabled($isStaff),
                            TextInput::make('billing_state')->label('Billing State/Emirate')->disabled($isStaff),
                            TextInput::make('billing_postcode')->label('Billing Postcode/P.O. Box')->disabled($isStaff),
                            TextInput::make('billing_country')->label('Billing Country')->disabled($isStaff),
                        ]),
                    ]),

                Section::make('Purchased Items & Variants')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                TextInput::make('product_name')->label('Product / Bundle Name')->disabled(),
                                TextInput::make('variant_details')
                                    ->label('Variant / Subtitle')
                                    ->formatStateUsing(function ($state, $record) {
                                        if (!empty($state)) return $state;
                                        if ($record && $record->product_name) {
                                            $deal = \App\Models\CuratedDeal::where('name', $record->product_name)->first();
                                            if ($deal && $deal->subtitle) return $deal->subtitle;
                                        }
                                        if ($record && $record->variant) {
                                            $v = $record->variant;
                                            return trim(($v->name ?? '') . ($v->size ? ' (' . $v->size . ($v->unit ? ' ' . $v->unit : '') . ')' : ''));
                                        }
                                        return 'Standard Edition';
                                    })
                                    ->disabled(),
                                TextInput::make('quantity')->label('Qty')->numeric()->disabled(),
                                TextInput::make('price')->label('Unit Price (AED)')->numeric()->prefix('AED ')->disabled(),
                                TextInput::make('line_total')->label('Line Total (AED)')->numeric()->prefix('AED ')->disabled(),
                            ])
                            ->columns(5)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                Section::make('Totals')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('subtotal')->numeric()->default(0.0)->prefix('AED ')->disabled($isStaff),
                            TextInput::make('shipping_cost')->numeric()->default(0.0)->prefix('AED ')->disabled($isStaff),
                            TextInput::make('discount')->numeric()->default(0.0)->prefix('AED ')->disabled($isStaff),
                            TextInput::make('coupon_code')->disabled($isStaff),
                            TextInput::make('grand_total')->numeric()->default(0.0)->prefix('AED ')->disabled($isStaff),
                        ]),
                        Textarea::make('notes')->columnSpanFull()->disabled($isStaff),
                    ]),
            ]);
    }
}

