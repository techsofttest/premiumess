<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWishlist;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerWishlistController extends Controller
{
    private function getAuthenticatedCustomer(Request $request): ?Customer
    {
        $user = Auth::guard('customer')->user();
        return $user instanceof Customer ? $user : null;
    }

    private function formatProduct(Product $product): array
    {
        $inStockVariants = $product->variants->filter(fn ($variant) => (int) $variant->stock > 0);
        $variant = $inStockVariants->first();
        
        $price = $variant ? (float) $variant->selling_price : 0.0;
        $weight = $variant ? trim(($variant->size ?? '') . ' ' . ($variant->unit ?? '')) : '1 unit';
        if (empty($weight)) {
            $weight = '1 unit';
        }
        
        $buyingPrice = $variant ? (float) ($variant->buying_price ?? 0) : 0.0;
        $originalPrice = ($buyingPrice > $price) ? $buyingPrice : null;
            
        $discount = ($originalPrice && $originalPrice > $price)
            ? (string) max(1, round((1 - ($price / $originalPrice)) * 100)) . '%'
            : null;

        $reviews = $product->reviews;
        $rating = round((float) ($reviews->avg('review_rating') ?: 0), 1);

        return [
            'id' => (string) $product->id,
            'slug' => $product->slug,
            'brand' => $product->brand ? $product->brand->name : null,
            'title' => $product->name,
            'image' => $product->featured_image ? asset('storage/' . $product->featured_image) : null,
            'weight' => $weight,
            'price' => $price,
            'originalPrice' => $originalPrice,
            'discount' => $discount,
            'rating' => $rating,
            'reviews' => $reviews->count(),
            'category' => $product->category ? $product->category->slug : null,
            'variants' => $inStockVariants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'unit' => $variant->unit,
                'size' => $variant->size,
                'price' => (float) $variant->selling_price,
                'stock' => (int) $variant->stock,
            ])->values()->all(),
        ];
    }

    private function getFormattedWishlist(Customer $customer): array
    {
        $products = $customer->wishlistedProducts()
            ->with(['brand', 'category', 'variants', 'reviews', 'images'])
            ->get();

        return $products->map(fn ($product) => $this->formatProduct($product))->all();
    }

    public function index(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return response()->json($this->getFormattedWishlist($customer));
    }

    public function store(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        CustomerWishlist::firstOrCreate([
            'customer_id' => $customer->id,
            'product_id' => $validated['product_id'],
        ]);

        return response()->json($this->getFormattedWishlist($customer));
    }

    public function destroy(Request $request, $productId): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        CustomerWishlist::where('customer_id', $customer->id)
            ->where('product_id', $productId)
            ->delete();

        return response()->json($this->getFormattedWishlist($customer));
    }
}
