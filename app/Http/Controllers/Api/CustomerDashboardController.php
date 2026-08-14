<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerDashboardController extends Controller
{
    private function getAuthenticatedCustomer(Request $request): ?Customer
    {
        $user = Auth::guard('customer')->user();

        return $user instanceof Customer ? $user : null;
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedCustomer($request);

        if (!($user instanceof Customer)) {
            return response()->json(['id' => null], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'registered_at' => optional($user->created_at)->toDateString(),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedCustomer($request);

        if (!($user instanceof Customer)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->password = $validated['new_password'];
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function dashboardSummary(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedCustomer($request);

        if (!($user instanceof Customer)) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Adjust these status values based on how you set them in UI/admin.
        $activeStatuses = ['processing', 'shipped', 'out_for_delivery', 'delivered'];
        $activeStatuses = array_map(fn ($s) => strtolower($s), $activeStatuses);

        $totalOrders = Order::query()->where('customer_id', $user->id)->count();

        $activeOrders = Order::query()
            ->where('customer_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->count();

        $savedAddressesCount = CustomerAddress::query()
            ->where('customer_id', $user->id)
            ->count();

        $last5Orders = Order::query()
            ->where('customer_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'order_number', 'created_at', 'status', 'grand_total']);

        return response()->json([
            'total_orders' => $totalOrders,
            'active_orders' => $activeOrders,
            'saved_addresses_count' => $savedAddressesCount,

            'wishlist_count' => $user->wishlistItems()->count(),
            'reward_points' => 0,

            'last_5_orders' => $last5Orders->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'order_date' => optional($o->created_at)->toDateString(),
                'status' => $o->status,
                'grand_total' => (float) $o->grand_total,
            ])->values(),
        ]);
    }

    private function resolveVariantDetails($item): string
    {
        $deal = \App\Models\CuratedDeal::where('name', $item->product_name)->first();
        if ($deal && $deal->subtitle) {
            return $deal->subtitle;
        }

        $vDetail = $item->variant_details ?? null;
        if (!empty($vDetail) && $vDetail !== 'null') {
            return $vDetail;
        }

        if (isset($item->variant) && $item->variant) {
            $v = $item->variant;
            $vDetail = trim(($v->name ?? '') . ($v->size ? ' (' . $v->size . ($v->unit ? ' ' . $v->unit : '') . ')' : ''));
            if (!$vDetail && !empty($v->sku)) {
                $vDetail = 'SKU: ' . $v->sku;
            }
        }
        return (string) ($vDetail ?: 'Special Curation Bundle');
    }

    public function showOrder(Request $request, $id): JsonResponse
    {
        $user = $this->getAuthenticatedCustomer($request);

        if (!($user instanceof Customer)) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::with(['items.product.brand', 'items.variant'])->where('customer_id', $user->id)->find($id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $timeSlot = null;
        if ($order->delivery_slot_id) {
            $slot = \App\Models\TimeSlot::find($order->delivery_slot_id);
            if ($slot) {
                $timeSlot = "{$slot->start_time} - {$slot->end_time}";
            }
        }

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'date' => optional($order->created_at)->format('F j, Y'),
            'status' => $order->status->value ?? $order->status,
            'payment_status' => $order->payment_status->value ?? $order->payment_status,
            'payment_method' => $order->payment_method,
            'subtotal' => (float)$order->subtotal,
            'shipping_cost' => (float)$order->shipping_cost,
            'discount' => (float)$order->discount,
            'grand_total' => (float)$order->grand_total,
            'delivery_type' => $order->delivery_type,
            'delivery_date' => $order->delivery_date,
            'time_slot' => $timeSlot,
            'address' => [
                'name' => $order->customer_name,
                'type' => 'Delivery Address',
                'street' => trim($order->address . ' ' . $order->apartment),
                'suburb' => trim($order->city . ', ' . $order->state . ' ' . $order->pin_code),
                'phone' => $order->phone,
            ],
            'items' => $order->items->map(function ($item) {
                $deal = \App\Models\CuratedDeal::where('name', $item->product_name)->first();
                $image = null;
                if ($deal && $deal->image) {
                    $image = str_starts_with($deal->image, 'http') ? $deal->image : asset($deal->image);
                } elseif ($item->product && $item->product->featured_image) {
                    $image = asset('storage/' . $item->product->featured_image);
                }

                return [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'price' => (float)$item->price,
                    'quantity' => $item->quantity,
                    'variant_details' => $this->resolveVariantDetails($item),
                    'weight' => $this->resolveVariantDetails($item),
                    'image' => $image,
                    'brand' => $deal ? 'Exclusive Curation' : ($item->product && $item->product->brand ? $item->product->brand->name : 'General'),
                ];
            })
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedCustomer($request);
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $query = Order::query()
            ->with(['items.product.brand', 'items.variant'])
            ->where('customer_id', $user->id);

        if ($paymentStatus = $request->string('payment_status')->toString()) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhereHas('items', fn ($itemQ) => $itemQ->where('product_name', 'like', '%' . $search . '%'));
            });
        }

        $orders = $query->latest()->get();

        return response()->json($orders->map(fn (Order $order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'date' => optional($order->created_at)->format('d M Y'),
            'status' => $order->status->value ?? $order->status,
            'payment_status' => $order->payment_status->value ?? $order->payment_status,
            'grand_total' => (float) $order->grand_total,
            'items_count' => $order->items->sum('quantity'),
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->product_name,
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'variant_details' => $this->resolveVariantDetails($item),
                'line_total' => (float) $item->line_total,
                'image' => $item->product && $item->product->featured_image ? asset('storage/' . $item->product->featured_image) : null,
                'brand' => $item->product && $item->product->brand ? $item->product->brand->name : 'Premium Essence',
            ]),
        ])->values());
    }
}

