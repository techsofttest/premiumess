<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_summary')
                    ->label('Products Ordered')
                    ->state(function (Order $record): string {
                        return $record->items->map(function ($item) {
                            $variantInfo = $item->variant_details ? " ({$item->variant_details})" : '';
                            return "{$item->product_name}{$variantInfo} x{$item->quantity}";
                        })->implode(', ');
                    })
                    ->limit(45)
                    ->tooltip(function (Order $record): string {
                        return $record->items->map(function ($item) {
                            $variantInfo = $item->variant_details ? " ({$item->variant_details})" : '';
                            $price = number_format((float) $item->price, 2);
                            $lineTotal = number_format((float) $item->line_total, 2);
                            return "• {$item->product_name}{$variantInfo} x{$item->quantity} @ {$price} AED = {$lineTotal} AED";
                        })->implode("\n");
                    })
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assignedStaff.name')
                    ->label('Assigned Staff')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivery_type')
                    ->label('Delivery')
                    ->badge()
                    ->color(fn ($state): string => $state === 'direct' ? 'success' : 'gray'),

                TextColumn::make('shipping_postcode')
                    ->label('Postcode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('AED')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(function ($state): string {
                        $value = $state instanceof \BackedEnum ? $state->value : (string) $state;
                        return match ($value) {
                            'pending' => 'warning',
                            'paid' => 'success',
                            'failed' => 'danger',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->color(function ($state): string {
                        $value = $state instanceof \BackedEnum ? $state->value : (string) $state;
                        return match ($value) {
                            'pending', 'pending_payment' => 'warning',
                            'confirmed' => 'success',
                            'processing' => 'info',
                            'packed' => 'primary',
                            'ready' => 'info',
                            'out_for_delivery' => 'primary',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // 1. Payment Status Filter (Default: 'paid' - completed payments)
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'paid' => 'Paid (Completed)',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ])
                    ->default('paid'),

                // 2. Order Fulfillment Status Filter
                SelectFilter::make('status')
                    ->label('Order Status')
                    ->options([
                        'confirmed' => 'Confirmed',
                        'pending' => 'Pending',
                        'pending_payment' => 'Pending Payment',
                        'processing' => 'Processing',
                        'packed' => 'Packed',
                        'ready' => 'Ready for Delivery',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                // 3. Filter by Product Ordered
                SelectFilter::make('product_id')
                    ->label('Product Ordered')
                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('items', function (Builder $itemQuery) use ($data) {
                            $itemQuery->where('product_id', $data['value']);
                        });
                    }),

                // 4. Date Range Filter (Created Date)
                Filter::make('created_at')
                    ->label('Order Date Range')
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

                // 5. Assigned Staff Filter
                SelectFilter::make('assigned_staff_id')
                    ->label('Assigned Staff')
                    ->options(function () {
                        return User::where('role', 'staff')
                            ->orWhereHas('roles', fn ($q) => $q->where('name', 'Staff'))
                            ->orWhereHas('permissions', fn ($q) => $q->where('name', 'delivery.driver'))
                            ->pluck('name', 'id');
                    }),

                Filter::make('unassigned')
                    ->label('Unassigned Orders')
                    ->query(fn (Builder $query) => $query->whereNull('assigned_staff_id')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                
                // Admin Action: Update Order & Payment Status Modal
                Action::make('updateOrderStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->form([
                        Select::make('status')
                            ->label('Order Status')
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
                            ->default(fn (Order $record) => $record->status->value ?? (string) $record->status)
                            ->required(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                                'partially_refunded' => 'Partially Refunded',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default(fn (Order $record) => $record->payment_status->value ?? (string) $record->payment_status)
                            ->required(),

                        Textarea::make('notes')
                            ->label('Admin Tracking Notes / Remark')
                            ->placeholder('Optional notes or tracking commentary...')
                            ->default(fn (Order $record) => $record->notes),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                            'payment_status' => $data['payment_status'],
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);

                        Notification::make()
                            ->title('Order Status Updated')
                            ->body("Order #{$record->order_number} status updated to " . strtoupper((string) $data['status']))
                            ->success()
                            ->send();
                    }),

                Action::make('assignStaff')
                    ->label('Assign Staff')
                    ->icon('heroicon-o-user')
                    ->visible(fn () => auth()->user()?->can('orders.assign') ?? false)
                    ->form([
                        Select::make('assigned_staff_id')
                            ->label('Staff Member')
                            ->options(function () {
                                return User::where('role', 'staff')
                                    ->orWhereHas('roles', fn ($q) => $q->where('name', 'Staff'))
                                    ->orWhereHas('permissions', fn ($q) => $q->where('name', 'delivery.driver'))
                                    ->pluck('name', 'id');
                            })
                            ->placeholder('Select a staff member')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update([
                            'assigned_staff_id' => $data['assigned_staff_id'],
                            'assigned_at' => now(),
                            'assigned_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Staff Assigned')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkUpdateStatus')
                        ->label('Update Order Statuses')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')
                                ->label('Order Status')
                                ->options([
                                    OrderStatus::PENDING_PAYMENT->value => 'Pending Payment',
                                    OrderStatus::CONFIRMED->value => 'Confirmed',
                                    OrderStatus::PROCESSING->value => 'Processing',
                                    OrderStatus::PACKED->value => 'Packed',
                                    OrderStatus::READY->value => 'Ready for Dispatch',
                                    OrderStatus::OUT_FOR_DELIVERY->value => 'Out for Delivery',
                                    OrderStatus::DELIVERED->value => 'Delivered',
                                    OrderStatus::CANCELLED->value => 'Cancelled',
                                    OrderStatus::REFUNDED->value => 'Refunded',
                                ])
                                ->required(),

                            Select::make('payment_status')
                                ->label('Payment Status')
                                ->options([
                                    'pending' => 'Pending',
                                    'paid' => 'Paid',
                                    'failed' => 'Failed',
                                    'refunded' => 'Refunded',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'status' => $data['status'],
                                    'payment_status' => $data['payment_status'],
                                ]);
                            });

                            Notification::make()
                                ->title('Orders Updated')
                                ->body("Updated {$records->count()} orders successfully.")
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('bulkAssignStaff')
                        ->label('Assign Staff')
                        ->icon('heroicon-o-user')
                        ->visible(fn () => auth()->user()?->can('orders.assign') ?? false)
                        ->form([
                            Select::make('assigned_staff_id')
                                ->label('Staff Member')
                                ->options(function () {
                                    return User::where('role', 'staff')
                                        ->orWhereHas('roles', fn ($q) => $q->where('name', 'Staff'))
                                        ->orWhereHas('permissions', fn ($q) => $q->where('name', 'delivery.driver'))
                                        ->pluck('name', 'id');
                                })
                                ->placeholder('Select a staff member')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'assigned_staff_id' => $data['assigned_staff_id'],
                                    'assigned_at' => now(),
                                    'assigned_by' => auth()->id(),
                                ]);
                            });
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
