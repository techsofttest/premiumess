<?php

namespace App\Enums;

enum TransactionType: string
{
    case PAYMENT_INTENT_CREATED = 'payment_intent_created';
    case CHECKOUT_SESSION_CREATED = 'checkout_session_created';
    case CHECKOUT_SESSION_COMPLETED = 'checkout.session.completed';
    case PAYMENT_INTENT_SUCCEEDED = 'payment_intent.succeeded';
    case PAYMENT_INTENT_PROCESSING = 'payment_intent.processing';
    case PAYMENT_INTENT_PAYMENT_FAILED = 'payment_intent.payment_failed';
    case PAYMENT_INTENT_CANCELED = 'payment_intent.canceled';
    case CHARGE_REFUNDED = 'charge.refunded';
}
