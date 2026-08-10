<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;
    
    protected string $view = 'filament.resources.orders.pages.order-detail';

    public function togglePicked($itemId)
    {
        $item = \App\Models\OrderItem::find($itemId);
        if ($item) {
            $item->update([
                'is_picked' => !$item->is_picked
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
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

            EditAction::make(),
        ];
    }
}
