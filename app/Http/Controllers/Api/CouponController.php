<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public static function checkValidity($couponCode, $subtotal, $customer = null)
    {
        $coupon = Coupon::where('coupon_code', $couponCode)->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid coupon code'];
        }

        if (isset($coupon->active) && !$coupon->active) {
            return ['valid' => false, 'message' => 'This coupon is inactive'];
        }

        $today = Carbon::today()->toDateString();
        if ($coupon->coupon_fromdate && $coupon->coupon_fromdate > $today) {
            return ['valid' => false, 'message' => 'This coupon is not active yet'];
        }

        if ($coupon->coupon_todate && $coupon->coupon_todate < $today) {
            return ['valid' => false, 'message' => 'This coupon has expired'];
        }

        if (isset($coupon->minimum_order_amount) && $subtotal < (float) $coupon->minimum_order_amount) {
            return [
                'valid' => false,
                'message' => 'Minimum order amount of ' . number_format($coupon->minimum_order_amount, 2) . ' AED is required to use this coupon'
            ];
        }

        // Global limit check
        if (isset($coupon->global_usage_limit) && $coupon->global_usage_limit > 0) {
            $usagesCount = $coupon->usages()->count();
            if ($usagesCount >= $coupon->global_usage_limit) {
                return ['valid' => false, 'message' => 'This coupon has reached its global usage limit'];
            }
        }

        // Customer limit check
        if ($customer) {
            if (isset($coupon->customer_usage_limit) && $coupon->customer_usage_limit > 0) {
                $customerUsages = $coupon->usages()->where('customer_id', $customer->id)->count();
                if ($customerUsages >= $coupon->customer_usage_limit) {
                    return ['valid' => false, 'message' => 'You have reached the usage limit for this coupon'];
                }
            }

            if (isset($coupon->first_order_only) && $coupon->first_order_only) {
                $hasPriorOrders = Order::where('customer_id', $customer->id)
                    ->where('payment_status', 'paid')
                    ->exists();
                if ($hasPriorOrders) {
                    return ['valid' => false, 'message' => 'This coupon is only available for your first order'];
                }
            }
        } else {
            // Guest checks
            if (isset($coupon->first_order_only) && $coupon->first_order_only) {
                return ['valid' => false, 'message' => 'Please log in. This coupon is only for first-time customers.'];
            }
            if (isset($coupon->customer_usage_limit) && $coupon->customer_usage_limit > 0) {
                return ['valid' => false, 'message' => 'Please log in to use this coupon.'];
            }
        }

        // Calculate discount
        $discount = 0;
        if ((int) $coupon->coupon_type === 1) { // 1 = Percentage
            $discount = round($subtotal * ((float) $coupon->coupon_amount / 100), 2);
            if (isset($coupon->maximum_discount) && $coupon->maximum_discount > 0 && $discount > (float) $coupon->maximum_discount) {
                $discount = (float) $coupon->maximum_discount;
            }
        } else { // Fixed
            $discount = min((float) $coupon->coupon_amount, $subtotal);
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Coupon applied successfully'
        ];
    }

    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['valid' => false, 'message' => 'Coupon code is required.'], 422);
        }

        $customer = Auth::guard('customer')->user();
        $subtotal = (float) ($request->input('subtotal') ?? 0);
        $couponCode = $request->input('coupon_code');

        $result = self::checkValidity($couponCode, $subtotal, $customer);

        if (!$result['valid']) {
            return response()->json(['valid' => false, 'message' => $result['message']], 422);
        }

        $coupon = $result['coupon'];

        return response()->json([
            'valid' => true,
            'coupon' => [
                'coupon_code' => $coupon->coupon_code,
                'coupon_type' => $coupon->coupon_type,
                'coupon_amount' => (float) $coupon->coupon_amount,
                'coupon_name' => $coupon->coupon_name,
            ],
            'discount' => $result['discount'],
            'message' => $result['message'],
        ]);
    }
}
