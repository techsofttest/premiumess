<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Services\DeliveryEligibilityService;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(
        protected PaymentGatewayInterface $paymentGateway,
        protected DeliveryEligibilityService $deliveryEligibilityService
    ) {
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.price' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'coupon_code' => 'nullable|string',
            'customer_email' => ['nullable', 'email', 'regex:/^[^\s@\/]+@[^\s@\/]+\.[^\s@\/]+$/'],
            'address' => 'required_without:delivery_details|array',
            'address.email' => ['nullable', 'email', 'regex:/^[^\s@\/]+@[^\s@\/]+\.[^\s@\/]+$/'],
            'address.address_line_2' => 'required_with:address|string|max:255',
            'delivery_details' => 'required_without:address|array',
            'delivery_details.email' => ['nullable', 'email', 'regex:/^[^\s@\/]+@[^\s@\/]+\.[^\s@\/]+$/'],
            'delivery_details.address_line_2' => 'required_with:delivery_details|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deliveryDetails = $request->input('address') ?? $request->input('delivery_details') ?? [];
        $postcode = $deliveryDetails['postcode'] ?? '2000';

        // Validate delivery availability for the cart and postcode
        $deliveryCheck = $this->deliveryEligibilityService->validateCart($postcode, $request->input('cart') ?? []);
        if (isset($deliveryCheck['valid']) && ! $deliveryCheck['valid']) {
            return response()->json(['error' => $deliveryCheck['message'] ?? 'Delivery not available for this postcode.'], 422);
        }

        // Determine delivery type (direct/courier)
        $deliveryType = $request->input('delivery_type') ?? ($deliveryCheck['delivery_type'] ?? null);

        // If direct delivery is required, ensure delivery_date and delivery_slot_id are provided and valid
        if ($deliveryType === 'direct') {
            $deliveryDate = $request->input('delivery_date');
            $deliverySlotId = $request->input('delivery_slot_id');

            $slotValid = false;
            $availableDates = $this->deliveryEligibilityService->getAvailableDatesAndSlots('direct');
            foreach ($availableDates as $d) {
                if (($d['date'] ?? null) === $deliveryDate) {
                    $slots = $d['slots'] ?? [];
                    foreach ($slots as $s) {
                        if (($s['id'] ?? null) == $deliverySlotId) {
                            $slotValid = true;
                            break 2;
                        }
                    }
                }
            }

            $errors = [];
            if (! $deliveryDate) $errors['delivery_date'] = ['Delivery date is required for direct delivery.'];
            if (! $deliverySlotId) $errors['delivery_slot_id'] = ['Delivery slot is required for direct delivery.'];
            if ($deliverySlotId && ! $slotValid) $errors['delivery_slot_id'] = ['Selected delivery slot is not available.'];

            if (! empty($errors)) {
                return response()->json(['errors' => $errors], 422);
            }
        }

        $subtotal = 0;
        foreach ($request->input('cart') as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shippingInfo = $this->deliveryEligibilityService->calculateShipping($postcode, $subtotal);
        $shippingCost = $shippingInfo['shipping_cost'] ?? 0;

        $customerId = $request->input('customer_id');
        if (! $customerId) {
            $customerId = session()->get('customer_id');
        }
        if (! $customerId) {
            $customerId = \Illuminate\Support\Facades\Auth::guard('customer')->id();
        }

        $discount = 0;
        $couponCode = $request->input('coupon_code');
        if ($couponCode) {
            $customer = null;
            if ($customerId) {
                $customer = \App\Models\Customer::find($customerId);
            }
            $couponResult = CouponController::checkValidity($couponCode, $subtotal, $customer);
            if (!$couponResult['valid']) {
                return response()->json(['error' => 'Coupon validation failed: ' . $couponResult['message']], 422);
            }
            $discount = (float) $couponResult['discount'];
        }

        $tax = 0;
        $grandTotal = max(0, $subtotal - $discount + $shippingCost);

        // Pre-checkout Real-Time Inventory Stock Check
        $cartItemsPayload = $request->input('cart', []);
        $stockErrors = [];
        foreach ($cartItemsPayload as $item) {
            $productId = $item['product_id'] ?? $item['productId'] ?? null;
            $variantId = $item['variant_id'] ?? $item['variantId'] ?? null;
            $qty = (int) ($item['quantity'] ?? 1);

            $product = \App\Models\Product::find($productId);
            if (!$product || !$product->is_active) {
                $stockErrors[] = "Product #" . $productId . " is no longer available.";
                continue;
            }

            $variant = null;
            if ($variantId) {
                $variant = \App\Models\ProductVariant::find($variantId);
            }
            if (!$variant) {
                $product->loadMissing('variants');
                $variant = $product->variants->first(fn($v) => (int) $v->stock > 0) ?? $product->variants->first();
            }

            if (!$variant) {
                $stockErrors[] = "No variant available for product '" . $product->name . "'.";
                continue;
            }

            $availableStock = (int) $variant->stock;
            $variantName = $variant->name ?: ($variant->sku ?: '');
            $displayName = $product->name . ($variantName ? " ({$variantName})" : '');

            if ($availableStock <= 0) {
                $stockErrors[] = "'" . $displayName . "' is currently out of stock.";
            } elseif ($qty > $availableStock) {
                $stockErrors[] = "Only " . $availableStock . " unit(s) of '" . $displayName . "' are available in stock (you requested " . $qty . ").";
            }
        }

        if (!empty($stockErrors)) {
            return response()->json([
                'valid' => false,
                'error' => implode(' ', $stockErrors),
                'stock_errors' => $stockErrors,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number' => 'TEMP-' . Str::upper(Str::random(10)),
                'customer_id' => $customerId,
                'customer_name' => $request->input('customer_name') ?? ($deliveryDetails['contact_name'] ?? $deliveryDetails['name'] ?? null),
                'customer_email' => $request->input('customer_email') ?? ($deliveryDetails['email'] ?? null),
                'customer_phone' => $request->input('customer_phone') ?? ($deliveryDetails['phone'] ?? null),
                
                // billing details
                'first_name' => explode(' ', ($deliveryDetails['contact_name'] ?? $deliveryDetails['name'] ?? 'Guest'), 2)[0] ?? 'Guest',
                'last_name' => explode(' ', ($deliveryDetails['contact_name'] ?? $deliveryDetails['name'] ?? 'Guest'), 2)[1] ?? '',
                'email' => $request->input('customer_email') ?? ($deliveryDetails['email'] ?? null),
                'phone' => $request->input('customer_phone') ?? ($deliveryDetails['phone'] ?? ''),
                'country' => $deliveryDetails['country'] ?? 'Australia',
                'address' => $deliveryDetails['address_line_1'] ?? ($deliveryDetails['address'] ?? ''),
                'apartment' => $deliveryDetails['address_line_2'] ?? null,
                'city' => $deliveryDetails['city'] ?? 'Sydney',
                'state' => $deliveryDetails['state'] ?? 'NSW',
                'pin_code' => $deliveryDetails['postcode'] ?? '2000',

                // shipping snapshot
                'shipping_name' => $deliveryDetails['contact_name'] ?? $deliveryDetails['name'] ?? null,
                'shipping_phone' => $deliveryDetails['phone'] ?? null,
                'shipping_address_line_1' => $deliveryDetails['address_line_1'] ?? ($deliveryDetails['address'] ?? null),
                'shipping_address_line_2' => $deliveryDetails['address_line_2'] ?? null,
                'shipping_suburb' => $deliveryDetails['suburb'] ?? null,
                'shipping_city' => $deliveryDetails['city'] ?? null,
                'shipping_state' => $deliveryDetails['state'] ?? null,
                'shipping_postcode' => $deliveryDetails['postcode'] ?? null,
                'shipping_country' => $deliveryDetails['country'] ?? 'Australia',
                'shipping_latitude' => $deliveryDetails['latitude'] ?? null,
                'shipping_longitude' => $deliveryDetails['longitude'] ?? null,
                'shipping_google_place_id' => $deliveryDetails['google_place_id'] ?? null,

                // delivery fulfillment
                'delivery_type' => $request->input('delivery_type') ?? ($this->deliveryEligibilityService->isDirectDeliveryPostcode($postcode) ? 'direct' : 'courier'),
                'delivery_slot_id' => $request->input('delivery_slot_id'),
                'delivery_date' => $request->input('delivery_date'),
                'delivery_notes' => $deliveryDetails['delivery_notes'] ?? ($request->input('notes') ?? null),
                'notes' => $deliveryDetails['delivery_notes'] ?? ($request->input('notes') ?? null),

                // payment
                'shipping_method' => $request->input('delivery_type') ?? ($this->deliveryEligibilityService->isDirectDeliveryPostcode($postcode) ? 'direct' : 'courier'),
                'payment_method' => $request->input('payment_method', 'card'),
                'payment_status' => 'pending',
                'status' => 'pending_payment',
                
                // pricing totals
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'coupon_code' => $couponCode,
                'discount' => $discount,
                'grand_total' => $grandTotal,
            ]);

            $order->update([
                'order_number' => 'TC-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            ]);

            // Create order items
            foreach ($request->input('cart') as $item) {
                $product = \App\Models\Product::find($item['product_id']);

                // Determine variant: prefer provided variant_id / variantId, otherwise pick a sensible fallback
                $variantId = $item['variant_id'] ?? $item['variantId'] ?? null;
                if (is_string($variantId)) {
                    $variantId = trim($variantId);
                    if ($variantId === '' || strtolower($variantId) === 'null') {
                        $variantId = null;
                    }
                }

                $variantDetails = null;
                $variant = null;

                if ($variantId !== null) {
                    $variant = \App\Models\ProductVariant::find($variantId);
                }

                if (!$variant && $product) {
                    // Ensure variants are loaded and try to pick an in-stock variant first
                    $product->loadMissing('variants');
                    $variant = $product->variants->first(fn($v) => (int) $v->stock > 0) ?? $product->variants->first();
                    if ($variant) {
                        $variantId = $variant->id;
                    }
                }

                if ($variant) {
                    $sizeStr = $variant->size ? trim($variant->size . ($variant->unit ? ' ' . $variant->unit : '')) : '';
                    $variantDetails = trim(($variant->name ?? '') . ($sizeStr ? ($variant->name ? ' - ' : '') . $sizeStr : ''));
                    if (!$variantDetails && $variant->sku) {
                        $variantDetails = 'SKU: ' . $variant->sku;
                    }
                }
                if (!$variantDetails && !empty($item['variant']) && is_string($item['variant'])) {
                    $variantDetails = $item['variant'];
                }

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $variantId !== null ? $variantId : null,
                    'product_name' => $product ? $product->name : 'Product #' . $item['product_id'],
                    'variant_details' => $variantDetails,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'line_total' => $item['price'] * $item['quantity'],
                ]);
            }

            $originUrl = $request->header('Origin') ?: 'http://localhost:3000';
            $successUrl = $request->input('success_url', $originUrl . '/checkout/success');
            $cancelUrl = $request->input('cancel_url', $originUrl . '/checkout');

            $checkoutSession = $this->paymentGateway->createCheckoutSession($order, $successUrl, $cancelUrl);

            DB::commit();

            return response()->json([
                'valid' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'checkout_url' => $checkoutSession['checkout_url'],
                'session_id' => $checkoutSession['session_id'],
                'payment_intent' => $checkoutSession,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create Stripe payment session: ' . $e->getMessage()], 500);
        }
    }

    public function retry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::find($request->input('order_id'));

        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'This order has already been paid.'], 400);
        }

        try {
            $paymentIntent = $this->paymentGateway->createPaymentIntent($order);

            return response()->json([
                'valid' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_intent' => $paymentIntent,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create payment intent: ' . $e->getMessage()], 500);
        }
    }

    public function paymentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'nullable|exists:orders,id',
            'order_number' => 'nullable|exists:orders,order_number',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = null;
        $orderId = $request->input('order_id');
        $orderNumber = $request->input('order_number');

        if ($orderId) {
            $order = Order::find($orderId);
        } elseif ($orderNumber) {
            $order = Order::where('order_number', $orderNumber)->first();
        }

        if (!$order) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        // Check if this is the first time viewing this status
        $sessionKey = "payment_status_viewed_{$order->id}";
        $hasViewed = session()->has($sessionKey);

        // Mark as viewed in session
        session()->put($sessionKey, true);

        $isSuccess = $order->payment_status === 'paid' || $order->payment_status === \App\Enums\PaymentStatus::PAID;
        if ($isSuccess) {
            $order->sendPaymentConfirmationEmails();
        }
        $isFailed = $order->payment_status === 'failed' || $order->payment_status === \App\Enums\PaymentStatus::FAILED;
        $isProcessing = $order->payment_status === 'processing';

        return response()->json([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status->value ?? $order->payment_status,
            'status' => $order->status,
            'is_success' => $isSuccess,
            'is_failed' => $isFailed,
            'is_processing' => $isProcessing,
            'is_first_view' => !$hasViewed,
            'grand_total' => $order->grand_total,
            'paid_at' => $order->paid_at,
            'payment_failure_reason' => $order->payment_failure_reason ?? null,
            'message' => $isSuccess 
                ? 'Payment successful! Your order has been confirmed.'
                : ($isFailed 
                    ? 'Payment failed. Please try again or contact support.'
                    : 'Payment is being processed. Please wait...'),
        ]);
    }

    public function trackOrder(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'order_number' => 'required|string',
            'email_or_phone' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        $orderNumber = trim($request->input('order_number'));
        $identifier = trim($request->input('email_or_phone'));

        $order = Order::with(['items.product.brand', 'items.variant'])
            ->where(function ($q) use ($orderNumber) {
                $q->where('order_number', $orderNumber)
                  ->orWhere('order_number', 'TC-' . str_pad($orderNumber, 6, '0', STR_PAD_LEFT))
                  ->orWhere('id', $orderNumber);
            })
            ->where(function ($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('customer_email', $identifier)
                  ->orWhere('phone', $identifier)
                  ->orWhere('customer_phone', $identifier)
                  ->orWhere('shipping_phone', $identifier);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'No order found matching the provided order number and email/phone number.'
            ], 404);
        }

        $timeSlot = null;
        if ($order->delivery_slot_id) {
            $slot = \App\Models\TimeSlot::find($order->delivery_slot_id);
            if ($slot) {
                $timeSlot = "{$slot->start_time} - {$slot->end_time}";
            }
        }

        $statusStr = strtolower($order->status->value ?? $order->status);
        $paymentStatusStr = strtolower($order->payment_status->value ?? $order->payment_status);

        $steps = [
            [
                'title' => 'Order Placed',
                'description' => 'Your order request has been received.',
                'completed' => true,
                'current' => $statusStr === 'pending_payment' || $statusStr === 'pending',
                'date' => optional($order->created_at)->format('M d, Y h:i A'),
            ],
            [
                'title' => 'Payment Confirmed',
                'description' => 'Payment verified & order confirmed.',
                'completed' => $paymentStatusStr === 'paid' || in_array($statusStr, ['confirmed', 'processing', 'packed', 'ready', 'out_for_delivery', 'delivered']),
                'current' => $statusStr === 'confirmed',
                'date' => $order->paid_at ? optional($order->paid_at)->format('M d, Y h:i A') : null,
            ],
            [
                'title' => 'Preparing Fragrances',
                'description' => 'Fragrances packed & inspected at Musaffah, M/9 Abu Dhabi warehouse.',
                'completed' => in_array($statusStr, ['processing', 'packed', 'ready', 'out_for_delivery', 'delivered']),
                'current' => in_array($statusStr, ['processing', 'packed', 'ready']),
                'date' => null,
            ],
            [
                'title' => 'Out for Delivery',
                'description' => 'Package handed to express courier or direct driver.',
                'completed' => in_array($statusStr, ['out_for_delivery', 'delivered']),
                'current' => $statusStr === 'out_for_delivery',
                'date' => null,
            ],
            [
                'title' => 'Delivered',
                'description' => 'Package successfully delivered.',
                'completed' => $statusStr === 'delivered',
                'current' => $statusStr === 'delivered',
                'date' => null,
            ],
        ];

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'date' => optional($order->created_at)->format('F j, Y'),
            'status' => $statusStr,
            'payment_status' => $paymentStatusStr,
            'payment_method' => $order->payment_method,
            'subtotal' => (float) $order->subtotal,
            'shipping_cost' => (float) $order->shipping_cost,
            'discount' => (float) $order->discount,
            'grand_total' => (float) $order->grand_total,
            'delivery_type' => $order->delivery_type,
            'delivery_date' => $order->delivery_date,
            'time_slot' => $timeSlot,
            'shipping_address' => [
                'name' => $order->customer_name ?: ($order->first_name . ' ' . $order->last_name),
                'address_line_1' => $order->shipping_address_line_1 ?: $order->address,
                'address_line_2' => $order->shipping_address_line_2 ?: $order->apartment,
                'city' => $order->shipping_city ?: $order->city,
                'state' => $order->shipping_state ?: $order->state,
                'postcode' => $order->shipping_postcode ?: $order->pin_code,
                'country' => $order->shipping_country ?: $order->country,
                'phone' => $order->shipping_phone ?: $order->phone,
            ],
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->product_name,
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'variant_details' => (function() use ($item) {
                    $vDetail = $item->variant_details;
                    if (!$vDetail && $item->variant) {
                        $v = $item->variant;
                        $vDetail = trim(($v->name ?? '') . ($v->size ? ' (' . $v->size . ($v->unit ? ' ' . $v->unit : '') . ')' : ''));
                        if (!$vDetail && $v->sku) {
                            $vDetail = 'SKU: ' . $v->sku;
                        }
                    }
                    return $vDetail ?: 'Standard Edition';
                })(),
                'line_total' => (float) $item->line_total,
                'image' => $item->product && $item->product->featured_image ? asset('storage/' . $item->product->featured_image) : null,
                'brand' => $item->product && $item->product->brand ? $item->product->brand->name : 'Premium Essence',
            ]),
            'timeline' => $steps,
        ]);
    }
}
