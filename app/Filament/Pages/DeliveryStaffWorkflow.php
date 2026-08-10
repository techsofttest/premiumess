<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Models\DeliverySession;
use App\Models\DeliverySessionOrder;
use App\Models\DeliveryComplianceLog;
use App\Models\Order;
use App\Enums\OrderStatus;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class DeliveryStaffWorkflow extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected string $view = 'filament.pages.delivery-staff-workflow';

    protected static ?string $navigationLabel = 'Driver Portal';
    protected static ?string $title = 'Driver Delivery Portal';
    protected static string|null|\UnitEnum $navigationGroup = 'Delivery';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->can('delivery.driver') || $user?->can('delivery.manage');
    }

    // State properties
    public $activeSession = null;
    public $currentStop = null;
    
    // Compliance & Completion Form fields
    public $tempComplianceEnabled = false;
    public $temperatureReading = null;
    public $thermometerPhoto = null;
    public $gpsLatitude = null;
    public $gpsLongitude = null;
    public $deliveryNotes = '';
    public $failureReason = '';

    // Modal state controllers
    public $showDeliveryModal = false;
    public $showFailureModal = false;

    public function mount(): void
    {
        $this->loadActiveSession();
    }


     public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public function loadActiveSession(): void
    {
        // Get the active session for the logged in staff
        $this->activeSession = DeliverySession::with(['sessionOrders.order.items'])
            ->where('status', 'in_progress')
            ->where('staff_id', auth()->id())
            ->first();

        if ($this->activeSession) {
            // Get the first pending stop
            $this->currentStop = $this->activeSession->sessionOrders()
                ->where('status', 'pending')
                ->first();
        } else {
            $this->currentStop = null;
        }
    }

    public function startSession($sessionId): void
    {
        $session = DeliverySession::find($sessionId);
        if ($session) {
            $session->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            // Update order statuses
            foreach ($session->sessionOrders as $so) {
                if ($so->order) {
                    $so->order->update([
                        'status' => OrderStatus::OUT_FOR_DELIVERY,
                    ]);
                }
            }

            Notification::make()->title('Session started! Stay safe on the road.')->success()->send();
            $this->loadActiveSession();
        }
    }

    public function openDeliveryModal(): void
    {
        $this->resetComplianceForm();
        $this->showDeliveryModal = true;
    }

    public function openFailureModal(): void
    {
        $this->failureReason = '';
        $this->showFailureModal = true;
    }

    public function resetComplianceForm(): void
    {
        $this->tempComplianceEnabled = false;
        $this->temperatureReading = null;
        $this->thermometerPhoto = null;
        $this->gpsLatitude = null;
        $this->gpsLongitude = null;
        $this->deliveryNotes = '';
    }

    public function submitDeliverySuccess(): void
    {
        if (!$this->currentStop) return;

        $photoPath = null;
        if ($this->tempComplianceEnabled && $this->thermometerPhoto) {
            $photoPath = $this->thermometerPhoto->store('compliance_photos', 'public');
        }

        // 1. Create compliance log if requested
        if ($this->tempComplianceEnabled) {
            DeliveryComplianceLog::create([
                'order_id' => $this->currentStop->order_id,
                'delivery_session_order_id' => $this->currentStop->id,
                'thermometer_photo' => $photoPath,
                'temperature_reading' => $this->temperatureReading,
                'latitude' => $this->gpsLatitude,
                'longitude' => $this->gpsLongitude,
                'captured_at' => now(),
                'notes' => $this->deliveryNotes,
            ]);
        }

        // 2. Update session order status
        $this->currentStop->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'notes' => $this->deliveryNotes,
        ]);

        // 3. Update main Order status to delivered
        if ($this->currentStop->order) {
            $this->currentStop->order->update([
                'status' => OrderStatus::DELIVERED,
            ]);
        }

        Notification::make()->title('Delivery logged successfully!')->success()->send();
        
        $this->showDeliveryModal = false;
        $this->loadActiveSession();
    }

    public function submitDeliveryFailure(): void
    {
        $this->validate([
            'failureReason' => 'required|string|min:3',
        ]);

        if (!$this->currentStop) return;

        $this->currentStop->update([
            'status' => 'failed',
            'failure_reason' => $this->failureReason,
            'notes' => 'Failed: ' . $this->failureReason,
        ]);

        Notification::make()->title('Order marked as failed.')->warning()->send();

        $this->showFailureModal = false;
        $this->loadActiveSession();
    }

    public function completeSession(): void
    {
        if ($this->activeSession) {
            $this->activeSession->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Notification::make()->title('Delivery session completed! Great job today.')->success()->send();
            $this->activeSession = null;
            $this->currentStop = null;
        }
    }

    public function getAvailableSessionsProperty()
    {
        return collect();
    }
}
