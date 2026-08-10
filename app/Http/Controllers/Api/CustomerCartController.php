<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerCart;
use App\Models\CustomerCartItem;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerCartController extends Controller
{
    private function cart(Request $request): CustomerCart
    {
        return CustomerCart::firstOrCreate(['customer_id' => Auth::guard('customer')->id()]);
    }

    private function payload(CustomerCart $cart): array
    {
        $cart->load(['items.product.brand', 'items.variant']);

        return $cart->items->map(function (CustomerCartItem $item) {
            $variant = $item->variant;
            $product = $item->product;

            $image = $product->featured_image ? asset('storage/' . $product->featured_image) : asset('products/Aventus 1.png');

            return [
                'id' => (string) $product->id,
                'cartItemId' => (string) $item->id,
                'productId' => $product->id,
                'variantId' => $variant->id,
                'brand' => $product->brand?->name ?? 'Premium Essence',
                'name' => $product->name,
                'price' => (float) ($variant->selling_price ?? 0),
                'size' => trim(($variant->size ?? '') . ($variant->unit ? ' ' . $variant->unit : '')),
                'image' => $image,
                'quantity' => (int) $item->quantity,
                'stock' => (int) ($variant->stock ?? 0),
            ];
        })->values()->all();
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->payload($this->cart($request)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        $variant = ProductVariant::whereKey($data['variant_id'])
            ->where('product_id', $data['product_id'])
            ->firstOrFail();

        if ($variant->stock < $data['quantity']) {
            return response()->json(['message' => 'The requested quantity is unavailable.'], 422);
        }

        $cart = $this->cart($request);
        $item = CustomerCartItem::firstOrNew([
            'customer_cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);
        $item->product_id = $data['product_id'];
        $item->quantity = min($variant->stock, ($item->exists ? $item->quantity : 0) + $data['quantity']);
        $item->save();

        return response()->json($this->payload($cart));
    }

    public function update(Request $request, CustomerCartItem $item): JsonResponse
    {
        abort_unless($item->customer_cart_id === $this->cart($request)->id, 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        if ($data['quantity'] > $item->variant->stock) {
            return response()->json(['message' => 'The requested quantity is unavailable.'], 422);
        }
        $item->update(['quantity' => $data['quantity']]);

        return response()->json($this->payload($item->cart));
    }

    public function destroy(Request $request, CustomerCartItem $item): JsonResponse
    {
        $cart = $this->cart($request);
        abort_unless($item->customer_cart_id === $cart->id, 404);
        $item->delete();

        return response()->json($this->payload($cart));
    }
}
