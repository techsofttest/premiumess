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
                            'pending' => 'Pending (Unpaid)',
                            'paid' => 'Paid (Completed)',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded',
                            'partially_refunded' => 'Partially Refunded',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default(fn (Order $record) => $record->payment_status->value ?? (string) $record->payment_status)
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('payment_reference')
                        ->label('Payment Reference / Receipt # / Transaction ID')
                        ->placeholder('e.g. COD-REC-109482 or Bank Ref #')
                        ->default(fn (Order $record) => $record->payment_reference ?? $record->stripe_payment_intent),

                    \Filament\Forms\Components\TextInput::make('payment_amount')
                        ->label('Collected Payment Amount (AED)')
                        ->numeric()
                        ->prefix('AED')
                        ->default(fn (Order $record) => $record->payment_amount ?: $record->grand_total),

                    Textarea::make('notes')
                        ->label('Admin Tracking Notes / Remark')
                        ->placeholder('Optional notes or tracking commentary...')
                        ->default(fn (Order $record) => $record->notes),
                ])
                ->action(function (Order $record, array $data): void {
                    $updateData = [
                        'status' => $data['status'],
                        'payment_status' => $data['payment_status'],
                        'notes' => $data['notes'] ?? $record->notes,
                    ];

                    if (!empty($data['payment_reference'])) {
                        $updateData['payment_reference'] = $data['payment_reference'];
                    }

                    if (isset($data['payment_amount']) && is_numeric($data['payment_amount'])) {
                        $updateData['payment_amount'] = (float) $data['payment_amount'];
                    }

                    $pStatus = $data['payment_status'] instanceof \BackedEnum ? $data['payment_status']->value : (string) $data['payment_status'];
                    if ($pStatus === 'paid' && !$record->paid_at) {
                        $updateData['paid_at'] = now();
                    }

                    $record->update($updateData);

                    // Create/log PaymentTransaction entry
                    if (!empty($data['payment_reference']) || $pStatus === 'paid') {
                        \App\Models\PaymentTransaction::create([
                            'order_id' => $record->id,
                            'gateway' => $record->payment_method ?: 'cod',
                            'transaction_type' => 'admin_payment_update',
                            'payment_intent' => $data['payment_reference'] ?? ($record->payment_reference ?? 'ADMIN-UPDATE'),
                            'status' => $pStatus,
                            'amount' => isset($data['payment_amount']) ? (float)$data['payment_amount'] : $record->grand_total,
                            'currency' => 'AED',
                        ]);
                    }

                    Notification::make()
                        ->title('Order Status & Payment Details Updated')
                        ->body("Order #{$record->order_number} status updated to " . strtoupper((string) $data['status']) . " (Payment: " . strtoupper($pStatus) . ")")
                        ->success()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}
