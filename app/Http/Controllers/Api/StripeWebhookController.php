<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewayInterface $paymentGateway
    ) {
    }

    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (!$sigHeader) {
            return response()->json(['error' => 'Missing Stripe-Signature header'], 400);
        }

        try {
            $this->paymentGateway->handleWebhook($payload, $sigHeader);
            return response()->json(['status' => 'success'], 200);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature: ' . $e->getMessage()], 400);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload: ' . $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Webhook processing failed: ' . $e->getMessage()], 500);
        }
    }
}
