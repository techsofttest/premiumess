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
            $cartKey = $item['id'] ?? ($productId ? ($productId . '_' . $variantId) : null);
            $dealSlug = $item['dealSlug'] ?? $item['deal_slug'] ?? null;
            if ($dealSlug && is_numeric($dealSlug)) {
                $dealSlug = null;
            }
            $dealId = $item['dealId'] ?? $item['deal_id'] ?? null;

            $isDeal = !empty($item['isDeal']) 
                || (is_string($cartKey) && str_starts_with($cartKey, 'deal-')) 
                || (is_string($productId) && str_starts_with($productId, 'deal-'))
                || (!empty($dealSlug) && !is_numeric($dealSlug))
                || !empty($dealId);

            if ($isDeal) {
                $slug = $dealSlug ?: (is_string($cartKey) ? str_replace('deal-', '', $cartKey) : (is_string($productId) ? str_replace('deal-', '', $productId) : ''));
                $deal = \App\Models\CuratedDeal::where('slug', $slug)->first();
                if (!$deal || !$deal->is_active) {
                    $stockErrors[] = "'" . ($item['name'] ?? 'Curated Deal') . "' is no longer available.";
                } else {
                    $dealStock = (int) ($deal->stock ?? 100);
                    if ($dealStock <= 0) {
                        $stockErrors[] = "'" . $deal->name . "' is currently out of stock.";
                    } elseif ($qty > $dealStock) {
                        $stockErrors[] = "Only " . $dealStock . " unit(s) of '" . $deal->name . "' are available in stock (you requested " . $qty . ").";
                    }
                }
                continue;
            }

            if (!$productId || !is_numeric($productId)) continue;

            $product = \App\Models\Product::find($productId);
            if (!$product || !$product->is_active) {
                $stockErrors[] = "'" . ($item['name'] ?? "Product #{$productId}") . "' is no longer available.";
                continue;
            }

            $variant = null;
            if ($variantId && is_numeric($variantId)) {
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

        $rawPaymentMethod = strtolower((string) $request->input('payment_method', 'stripe'));
        $isCod = in_array($rawPaymentMethod, ['cod', 'cash_on_delivery', 'cash']);

        $billingSameAsShipping = filter_var($request->input('billing_same_as_shipping', true), FILTER_VALIDATE_BOOLEAN);
        $billingDetailsInput = (array) $request->input('billing_details', []);
        $billingDetails = ($billingSameAsShipping || empty($billingDetailsInput)) ? $deliveryDetails : $billingDetailsInput;

        $billingName = $billingDetails['contact_name'] ?? $billingDetails['name'] ?? ($deliveryDetails['contact_name'] ?? $deliveryDetails['name'] ?? null);
        $billingPhone = $billingDetails['phone'] ?? ($deliveryDetails['phone'] ?? '');
        $billingAddress1 = $billingDetails['address_line_1'] ?? ($billingDetails['address'] ?? '');
        $billingAddress2 = $billingDetails['address_line_2'] ?? null;
        $billingCity = $billingDetails['city'] ?? 'Abu Dhabi';
        $billingState = $billingDetails['state'] ?? 'Abu Dhabi';
        $billingPostcode = $billingDetails['postcode'] ?? '00000';
        $billingCountry = $billingDetails['country'] ?? 'United Arab Emirates';

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number' => 'TEMP-' . Str::upper(Str::random(10)),
                'customer_id' => $customerId,
                'customer_name' => $request->input('customer_name') ?? ($deliveryDetails['contact_name'] ?? $deliveryDetails['name'] ?? null),
                'customer_email' => $request->input('customer_email') ?? ($deliveryDetails['email'] ?? null),
                'customer_phone' => $request->input('customer_phone') ?? ($deliveryDetails['phone'] ?? null),
                
                // billing legacy details
                'first_name' => explode(' ', ($billingName ?? 'Guest'), 2)[0] ?? 'Guest',
                'last_name' => explode(' ', ($billingName ?? 'Guest'), 2)[1] ?? '',
                'email' => $request->input('customer_email') ?? ($deliveryDetails['email'] ?? null),
                'phone' => $billingPhone,
                'country' => $billingCountry,
                'address' => $billingAddress1,
                'apartment' => $billingAddress2,
                'city' => $billingCity,
                'state' => $billingState,
                'pin_code' => $billingPostcode,

                // explicit billing snapshot
                'billing_same_as_shipping' => $billingSameAsShipping,
                'billing_name' => $billingName,
                'billing_phone' => $billingPhone,
                'billing_address_line_1' => $billingAddress1,
                'billing_address_line_2' => $billingAddress2,
                'billing_city' => $billingCity,
                'billing_state' => $billingState,
                'billing_postcode' => $billingPostcode,
                'billing_country' => $billingCountry,

                // shipping snapshot
                'shipping_name' => $deliveryDetails['contact_name'] ?? $deliveryDetails['name'] ?? null,
                'shipping_phone' => $deliveryDetails['phone'] ?? null,
                'shipping_address_line_1' => $deliveryDetails['address_line_1'] ?? ($deliveryDetails['address'] ?? null),
                'shipping_address_line_2' => $deliveryDetails['address_line_2'] ?? null,
                'shipping_suburb' => $deliveryDetails['suburb'] ?? null,
                'shipping_city' => $deliveryDetails['city'] ?? 'Abu Dhabi',
                'shipping_state' => $deliveryDetails['state'] ?? 'Abu Dhabi',
                'shipping_postcode' => $deliveryDetails['postcode'] ?? '00000',
                'shipping_country' => $deliveryDetails['country'] ?? 'United Arab Emirates',
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
                'payment_method' => $isCod ? 'cod' : 'stripe',
                'payment_status' => 'pending',
                'status' => $isCod ? 'confirmed' : 'pending_payment',
                
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
            $fallbackProduct = \App\Models\Product::query()->where('is_active', true)->first();
            $fallbackProductId = $fallbackProduct ? $fallbackProduct->id : 1;

            foreach ($request->input('cart') as $item) {
                $productId = $item['product_id'] ?? $item['productId'] ?? null;
                $variantId = $item['variant_id'] ?? $item['variantId'] ?? null;
                if (is_string($variantId)) {
                    $variantId = trim($variantId);
                    if ($variantId === '' || strtolower($variantId) === 'null') {
                        $variantId = null;
                    }
                }

                $cartKey = $item['id'] ?? ($productId ? ($productId . '_' . $variantId) : null);
                $dealSlug = $item['dealSlug'] ?? $item['deal_slug'] ?? null;
                $dealId = $item['dealId'] ?? $item['deal_id'] ?? null;

                $isDeal = !empty($item['isDeal']) 
                    || (is_string($cartKey) && str_starts_with($cartKey, 'deal-')) 
                    || (is_string($productId) && str_starts_with($productId, 'deal-'))
                    || !empty($dealSlug)
                    || !empty($dealId)
                    || (empty($variantId) && $variantId === null);

                $validProductId = (is_numeric($productId) && \App\Models\Product::where('id', $productId)->exists())
                    ? (int) $productId
                    : $fallbackProductId;

                $productName = !empty($item['name']) ? trim($item['name']) : null;
                $variantDetails = $item['size'] ?? ($item['variant'] ?? null);

                if ($isDeal || empty($variantId)) {
                    // Search Curated Deals table for related deal product when variant_id is empty
                    $dealModel = null;

                    if ($dealId && is_numeric($dealId)) {
                        $dealModel = \App\Models\CuratedDeal::find($dealId);
                    }

                    if (!$dealModel && $dealSlug) {
                        $dealModel = \App\Models\CuratedDeal::where('slug', $dealSlug)->first();
                    }

                    if (!$dealModel && is_string($cartKey) && str_starts_with($cartKey, 'deal-')) {
                        $slugFromKey = str_replace('deal-', '', $cartKey);
                        $dealModel = \App\Models\CuratedDeal::where('slug', $slugFromKey)->first();
                        if (!$dealModel && is_numeric($slugFromKey)) {
                            $dealModel = \App\Models\CuratedDeal::find($slugFromKey);
                        }
                    }

                    if (!$dealModel && is_string($productId) && str_starts_with($productId, 'deal-')) {
                        $slugFromPid = str_replace('deal-', '', $productId);
                        $dealModel = \App\Models\CuratedDeal::where('slug', $slugFromPid)->first();
                        if (!$dealModel && is_numeric($slugFromPid)) {
                            $dealModel = \App\Models\CuratedDeal::find($slugFromPid);
                        }
                    }

                    if (!$dealModel && $productName) {
                        $dealModel = \App\Models\CuratedDeal::where('name', $productName)->first();
                    }

                    if ($dealModel) {
                        $productName = $dealModel->name;
                        if (!$variantDetails) {
                            $variantDetails = $dealModel->subtitle ?: 'Exclusive Curation Bundle';
                        }
                    } elseif (!$productName) {
                        $productName = 'Curated Special Deal';
                    }
                } elseif (!$productName && is_numeric($productId)) {
                    $product = \App\Models\Product::find($productId);
                    if ($product) {
                        $productName = $product->name;
                    }
                }

                if (!$productName) {
                    $productName = 'Curated Special Deal';
                }

                $finalVariantId = (is_numeric($variantId) && $variantId !== null) ? (int) $variantId : null;

                if (!$isDeal && $finalVariantId && is_numeric($productId)) {
                    $product = \App\Models\Product::find($productId);
                    if ($product) {
                        $variant = \App\Models\ProductVariant::find($finalVariantId);
                        if ($variant) {
                            $sizeStr = $variant->size ? trim($variant->size . ($variant->unit ? ' ' . $variant->unit : '')) : '';
                            $vStr = trim(($variant->name ?? '') . ($sizeStr ? ($variant->name ? ' - ' : '') . $sizeStr : ''));
                            if (!$vStr && $variant->sku) {
                                $vStr = 'SKU: ' . $variant->sku;
                            }
                            if ($vStr) {
                                $variantDetails = $vStr;
                            }
                        }
                    }
                }

                $order->items()->create([
                    'product_id' => $validProductId,
                    'variant_id' => $finalVariantId,
                    'product_name' => $productName,
                    'variant_details' => $variantDetails ?: 'Standard Edition',
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                    'line_total' => (float) ($item['price'] * $item['quantity']),
                ]);
            }

            if ($isCod) {
                // Reduce stock for products and curated deals immediately upon COD order placement
                $order->reduceInventoryStock();

                // Create COD transaction record
                \App\Models\PaymentTransaction::create([
                    'order_id' => $order->id,
                    'gateway' => 'cod',
                    'transaction_type' => 'cash_on_delivery',
                    'payment_intent' => 'COD-' . $order->order_number,
                    'status' => 'pending',
                    'amount' => $order->grand_total,
                    'currency' => 'AED',
                ]);

                // Clear DB cart if customer is logged in
                if ($customerId) {
                    \App\Models\CustomerCartItem::whereHas('cart', function ($q) use ($customerId) {
                        $q->where('customer_id', $customerId);
                    })->delete();
                }

                // Send email notifications
                try {
                    $order->sendPaymentConfirmationEmails();
                } catch (\Throwable $e) {
                    // Ignore email error during COD order creation
                }

                DB::commit();

                return response()->json([
                    'valid' => true,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'checkout_url' => null,
                    'message' => 'Order placed successfully with Cash on Delivery.',
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
        session()->put($sessionKey, true);

        $paymentMethod = strtolower((string) ($order->payment_method ?? 'stripe'));
        $isCod = in_array($paymentMethod, ['cod', 'cash_on_delivery', 'cash']);
        $isPaid = $order->payment_status === 'paid' || $order->payment_status === \App\Enums\PaymentStatus::PAID;
        $orderStatusStr = is_object($order->status) ? $order->status->value : (string) $order->status;
        $isConfirmed = $isCod || in_array($orderStatusStr, ['confirmed', 'processing', 'packed', 'ready', 'out_for_delivery', 'delivered']);
        $isSuccess = $isPaid || $isConfirmed;

        if ($isSuccess) {
            $order->sendPaymentConfirmationEmails();
        }

        $isFailed = $order->payment_status === 'failed' || $order->payment_status === \App\Enums\PaymentStatus::FAILED;
        $isProcessing = $order->payment_status === 'processing';

        $message = $isCod
            ? 'Your order has been placed and confirmed successfully! We are preparing your luxury fragrances with meticulous care. Payment will be collected in cash upon doorstep delivery.'
            : ($isSuccess
                ? 'Your payment was processed successfully! We are preparing your luxury fragrances with meticulous care.'
                : ($isFailed
                    ? 'Payment failed. Please try again or contact support.'
                    : 'Payment is being processed. Please wait...'));

        return response()->json([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $isCod ? 'Pending (COD)' : ($order->payment_status->value ?? $order->payment_status),
            'payment_method' => $order->payment_method,
            'is_cod' => $isCod,
            'status' => $orderStatusStr,
            'is_success' => $isSuccess,
            'is_failed' => $isFailed,
            'is_processing' => $isProcessing,
            'is_first_view' => !$hasViewed,
            'grand_total' => (float) $order->grand_total,
            'paid_at' => $order->paid_at,
            'payment_failure_reason' => $order->payment_failure_reason ?? null,
            'message' => $message,
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
