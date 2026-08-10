<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeliveryEligibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryEligibilityController extends Controller
{
    public function __construct(
        protected DeliveryEligibilityService $deliveryEligibilityService
    ) {
    }

    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'postcode' => ['required', 'string', 'max:20'],
            'cart' => ['required', 'array', 'min:1'],
        ]);

        $result = $this->deliveryEligibilityService->validateCart(
            $data['postcode'],
            $data['cart']
        );

        return response()->json($result, $result['valid'] ? 200 : 422);
    }
}
