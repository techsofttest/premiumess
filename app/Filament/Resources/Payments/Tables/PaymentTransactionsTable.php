<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->order_id ? "/admin/orders/{$record->order_id}" : null),

                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->sortable(),

                TextColumn::make('transaction_type')
                    ->label('Event / Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_intent')
                    ->label('Stripe Payment Intent')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('charge_id')
                    ->label('Stripe Charge')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match (stringvalue($state)) {
                        'succeeded', 'paid' => 'success',
                        'failed' => 'danger',
                        'refunded', 'partially_refunded' => 'warning',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('AED')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // 1. Transaction Status Filter
                SelectFilter::make('status')
                    ->label('Transaction Status')
                    ->options([
                        'succeeded' => 'Succeeded',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'partially_refunded' => 'Partially Refunded',
                        'canceled' => 'Canceled',
                    ]),

                // 2. Event / Type Filter
                SelectFilter::make('transaction_type')
                    ->label('Event / Type')
                    ->options([
                        'checkout_session_created' => 'Checkout Session Created',
                        'checkout.session.completed' => 'Checkout Session Completed',
                        'payment_intent.succeeded' => 'Payment Succeeded',
                        'payment_intent_created' => 'Intent Created',
                        'payment_intent.payment_failed' => 'Payment Failed',
                        'charge.refunded' => 'Charge Refunded',
                        'payment_intent.canceled' => 'Intent Canceled',
                    ]),

                // 3. Gateway Filter
                SelectFilter::make('gateway')
                    ->label('Gateway')
                    ->options([
                        'stripe' => 'Stripe',
                    ]),

                // 4. Date Range Filter
                Filter::make('created_at')
                    ->label('Transaction Date Range')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('From Date'),
                        DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
            ]);
    }
}

function stringvalue($val): string
{
    if (is_object($val) && property_exists($val, 'value')) {
        return (string) $val->value;
    }
    return (string) $val;
}
