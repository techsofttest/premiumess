<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Models\Order;
use App\Models\DeliverySession;
use App\Models\DeliverySessionOrder;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Carbon\Carbon;

class DeliveryDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.delivery-dashboard';

    protected static ?string $navigationLabel = 'Delivery Dashboard';
    protected static ?string $title = 'Delivery Operations Dashboard';
    protected static string|null|\UnitEnum $navigationGroup = 'Delivery';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('delivery.manage') ?? false;
    }

    public ?int $selectedSessionId = null;

    public function mount(): void
    {
        // Default to the first active/in-progress session, or the latest session
        $this->selectedSessionId = DeliverySession::where('status', 'in_progress')
            ->first()?->id ?? DeliverySession::latest()->first()?->id;

        $this->dispatchMapUpdate();
    }
    
    public function updatedSelectedSessionId($value)
    {
        $this->selectedSessionId = $value;
        $this->dispatchMapUpdate();
    }

    public function dispatchMapUpdate(): void
    {
        $this->dispatch('map-update', $this->getMapData());
    }

    public function getStats(): array
    {
        $today = Carbon::today();

        $todayOrders = Order::whereDate('created_at', $today)->count();
        
        $readyForDispatch = Order::where('status', OrderStatus::READY)
            ->where('payment_status', PaymentStatus::PAID)
            ->count();

        // Count morning sessions (slot start time before 12:00)
        $morningSessionsOrders = DeliverySessionOrder::whereHas('deliverySession', function ($query) use ($today) {
            $query->whereDate('delivery_date', $today)
                ->whereHas('timeSlot', function ($q) {
                    $q->where('start_time', '<', '12:00:00');
                });
        })->count();

        // Count afternoon sessions (slot start time after/equal 12:00)
        $afternoonSessionsOrders = DeliverySessionOrder::whereHas('deliverySession', function ($query) use ($today) {
            $query->whereDate('delivery_date', $today)
                ->whereHas('timeSlot', function ($q) {
                    $q->where('start_time', '>=', '12:00:00');
                });
        })->count();

        $deliveredCount = DeliverySessionOrder::where('status', 'delivered')
            ->whereDate('delivered_at', $today)
            ->count();

        $pendingCount = DeliverySessionOrder::where('status', 'pending')->count();

        $failedCount = DeliverySessionOrder::where('status', 'failed')
            ->whereDate('updated_at', $today)
            ->count();

        return [
            'today_orders' => $todayOrders,
            'ready_for_dispatch' => $readyForDispatch,
            'morning_session' => $morningSessionsOrders,
            'afternoon_session' => $afternoonSessionsOrders,
            'delivered' => $deliveredCount,
            'pending' => $pendingCount,
            'failed' => $failedCount,
        ];
    }

    public function getSessionsProperty()
    {
        return DeliverySession::with('timeSlot')
            ->latest()
            ->take(10)
            ->get();
    }

    public function getMapData(): array
    {
        $store = [
            'name' => 'Store / Warehouse',
            'lat' => (float)config('delivery.store_coordinates.latitude', -37.8136),
            'lng' => (float)config('delivery.store_coordinates.longitude', 144.9631),
        ];

        $stops = [];

        if ($this->selectedSessionId) {
            $sessionOrders = DeliverySessionOrder::with('order')
                ->where('delivery_session_id', $this->selectedSessionId)
                ->orderBy('stop_sequence')
                ->get();

            foreach ($sessionOrders as $so) {
                $order = $so->order;
                if ($order) {
                    // Check if coordinates exist or randomize near store for demo
                    $lat = $order->shipping_latitude;
                    $lng = $order->shipping_longitude;
                    if (!$lat || !$lng) {
                        $lat = $store['lat'] + (rand(-40, 40) / 1000);
                        $lng = $store['lng'] + (rand(-40, 40) / 1000);
                    }

                    $stops[] = [
                        'sequence' => $so->stop_sequence,
                        'order_number' => $order->order_number,
                        'customer' => $order->customer_name,
                        'address' => $order->shipping_address_line_1,
                        'lat' => (float)$lat,
                        'lng' => (float)$lng,
                        'status' => $so->status,
                        'eta' => $so->eta,
                    ];
                }
            }
        }

        return [
            'store' => $store,
            'stops' => $stops,
        ];
    }
}
