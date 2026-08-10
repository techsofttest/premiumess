<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('defaultShippingAddress.phone')
                    ->searchable()
                    ->label('Phone'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('defaultShippingAddress.contact_name')
                    ->label('Address')
                    ->formatStateUsing(function ($state, $record) {
                        $address = $record->defaultShippingAddress;
                        if (!$address) {
                            return 'Not Added';
                        }
                        
                        $formatted = [];
                        if ($address->contact_name) {
                            $formatted[] = $address->contact_name;
                        }
                        if ($address->address_line_1) {
                            $formatted[] = $address->address_line_1;
                        }
                        if ($address->address_line_2) {
                            $formatted[] = $address->address_line_2;
                        }
                        if ($address->suburb) {
                            $formatted[] = $address->suburb;
                        }
                        if ($address->city) {
                            $formatted[] = $address->city;
                        }
                        if ($address->state) {
                            $formatted[] = $address->state;
                        }
                        if ($address->postcode) {
                            $formatted[] = $address->postcode;
                        }
                        return implode(', ', $formatted);
                    })
                    ->html()
                    ->wrap()
                    ->tooltip(function ($record) {
                        if (!$record->defaultShippingAddress) {
                            return 'No default address set';
                        }
                        return null;
                    }),

                TextColumn::make('orders_count')
                    ->label('Total Orders')
                    ->getStateUsing(function ($record) {
                        return $record->orders()->count();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('orders')->orderBy('orders_count', $direction);
                    }),
                TextColumn::make('last_order_date')
                    ->label('Last Order Date')
                    ->getStateUsing(function ($record) {
                        $lastOrder = $record->orders()->latest('created_at')->first();
                        if (!$lastOrder) {
                            return 'No Orders Yet';
                        }
                        return $lastOrder->created_at->format('d-m-Y');
                    }),
                TextColumn::make('defaultShippingAddress.delivery_notes')
                    ->label('Delivery Notes')
                    ->searchable()
                    ->wrap()
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                // No bulk actions for view only
            ]);
    }
}
