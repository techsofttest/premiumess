<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::post('/customer/logout', function (Request $request) {
    Auth::guard('customer')->logout();

    $request->session()->forget('customer_id');
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->back();
});

Route::post('/webhooks/stripe', [\App\Http\Controllers\Api\StripeWebhookController::class, 'handle']);

