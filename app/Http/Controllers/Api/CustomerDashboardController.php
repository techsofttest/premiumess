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

        // Active orders: all confirmed or in-progress orders that have not yet been delivered, cancelled, or refunded
        $totalOrders = Order::query()->where('customer_id', $user->id)->count();

        $activeOrders = Order::query()
            ->where('customer_id', $user->id)
            ->whereNotIn('status', ['delivered', 'cancelled', 'refund_requested', 'refunded'])
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', '!=', 'pending_payment');
            })
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
                'status' => $o->status->value ?? $o->status,
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

    private function resolveItemImage($item): ?string
    {
        $deal = $item->getCuratedDeal() 
            ?? \App\Models\CuratedDeal::where('name', $item->product_name)->first()
            ?? ($item->product_id ? \App\Models\CuratedDeal::find($item->product_id) : null);

        if ($deal && $deal->image) {
            if (str_starts_with($deal->image, 'http://') || str_starts_with($deal->image, 'https://')) {
                return $deal->image;
            }
            $cleanPath = ltrim(preg_replace('/^storage\//', '', $deal->image), '/');
            return asset('storage/' . $cleanPath);
        }

        if ($item->product && $item->product->featured_image) {
            if (str_starts_with($item->product->featured_image, 'http://') || str_starts_with($item->product->featured_image, 'https://')) {
                return $item->product->featured_image;
            }
            $cleanPath = ltrim(preg_replace('/^storage\//', '', $item->product->featured_image), '/');
            return asset('storage/' . $cleanPath);
        }

        return asset('images/logo/brand-logo-nobg.png');
    }

    private function formatShippingAddress(Order $order): array
    {
        $name = $order->shipping_name ?: ($order->customer_name ?: trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? '')));
        $phone = $order->shipping_phone ?: ($order->customer_phone ?: $order->phone);
        $street = trim(($order->shipping_address_line_1 ?: $order->address) . (($order->shipping_address_line_2 || $order->apartment) ? ', ' . ($order->shipping_address_line_2 ?: $order->apartment) : ''));

        return [
            'name' => $name ?: 'Customer',
            'phone' => $phone ?: '',
            'street' => $street ?: '',
            'suburb' => $order->shipping_suburb ?: null,
            'city' => $order->shipping_city ?: $order->city,
            'state' => $order->shipping_state ?: $order->state,
            'postcode' => $order->shipping_postcode ?: $order->pin_code,
            'country' => $order->shipping_country ?: $order->country,
        ];
    }

    private function formatBillingAddress(Order $order, array $shippingAddress): array
    {
        $billingStreet = trim(($order->billing_address_line_1 ?: '') . ($order->billing_address_line_2 ? ', ' . $order->billing_address_line_2 : ''));
        $isSame = (bool) $order->billing_same_as_shipping;

        return [
            'name' => ($isSame || !$order->billing_name) ? $shippingAddress['name'] : $order->billing_name,
            'phone' => ($isSame || !$order->billing_phone) ? $shippingAddress['phone'] : $order->billing_phone,
            'street' => ($isSame || !$billingStreet) ? $shippingAddress['street'] : $billingStreet,
            'suburb' => $isSame ? $shippingAddress['suburb'] : ($order->billing_suburb ?? $shippingAddress['suburb']),
            'city' => ($isSame || !$order->billing_city) ? $shippingAddress['city'] : $order->billing_city,
            'state' => ($isSame || !$order->billing_state) ? $shippingAddress['state'] : $order->billing_state,
            'postcode' => ($isSame || !$order->billing_postcode) ? $shippingAddress['postcode'] : $order->billing_postcode,
            'country' => ($isSame || !$order->billing_country) ? $shippingAddress['country'] : $order->billing_country,
        ];
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

        $shippingAddress = $this->formatShippingAddress($order);
        $billingAddress = $this->formatBillingAddress($order, $shippingAddress);

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'date' => optional($order->created_at)->format('F j, Y'),
            'status' => $order->status->value ?? $order->status,
            'payment_status' => $order->payment_status->value ?? $order->payment_status,
            'payment_method' => $order->payment_method ?: 'N/A',
            'subtotal' => (float)$order->subtotal,
            'shipping_cost' => (float)$order->shipping_cost,
            'discount' => (float)$order->discount,
            'grand_total' => (float)$order->grand_total,
            'delivery_type' => $order->delivery_type,
            'delivery_date' => $order->delivery_date,
            'time_slot' => $timeSlot,
            'billing_same_as_shipping' => (bool) $order->billing_same_as_shipping,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
            'address' => [
                'name' => $shippingAddress['name'],
                'type' => 'Delivery Address',
                'street' => $shippingAddress['street'],
                'suburb' => trim(($shippingAddress['city'] ?? '') . ', ' . ($shippingAddress['state'] ?? '') . ' ' . ($shippingAddress['postcode'] ?? '')),
                'phone' => $shippingAddress['phone'],
            ],
            'items' => $order->items->map(function ($item) {
                $deal = $item->getCuratedDeal() ?? \App\Models\CuratedDeal::where('name', $item->product_name)->first();
                return [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'price' => (float)$item->price,
                    'quantity' => $item->quantity,
                    'variant_details' => $this->resolveVariantDetails($item),
                    'weight' => $this->resolveVariantDetails($item),
                    'image' => $this->resolveItemImage($item),
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
            if ($status === 'active') {
                $query->whereNotIn('status', ['delivered', 'cancelled', 'refund_requested', 'refunded'])
                      ->where(function ($q) {
                          $q->where('payment_status', 'paid')
                            ->orWhere('status', '!=', 'pending_payment');
                      });
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhereHas('items', fn ($itemQ) => $itemQ->where('product_name', 'like', '%' . $search . '%'));
            });
        }

        $orders = $query->latest()->get();

        return response()->json($orders->map(function (Order $order) {
            $shippingAddress = $this->formatShippingAddress($order);
            $billingAddress = $this->formatBillingAddress($order, $shippingAddress);

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'date' => optional($order->created_at)->format('d M Y'),
                'status' => $order->status->value ?? $order->status,
                'payment_status' => $order->payment_status->value ?? $order->payment_status,
                'payment_method' => $order->payment_method ?: 'N/A',
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'discount' => (float) $order->discount,
                'grand_total' => (float) $order->grand_total,
                'items_count' => $order->items->sum('quantity'),
                'billing_same_as_shipping' => (bool) $order->billing_same_as_shipping,
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'variant_details' => $this->resolveVariantDetails($item),
                    'line_total' => (float) $item->line_total,
                    'image' => $this->resolveItemImage($item),
                    'brand' => ($item->getCuratedDeal() || \App\Models\CuratedDeal::where('name', $item->product_name)->exists()) ? 'Exclusive Curation' : ($item->product && $item->product->brand ? $item->product->brand->name : 'Premium Essence'),
                ]),
            ];
        })->values());
    }
}
