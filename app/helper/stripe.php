<?php
use Illuminate\Http\Request;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Facades\Log;

function stripe($array = [])
{
    // Set Stripe API key
    Stripe::setApiKey(env('STRIPE_SECRET'));

    // Create a new payment intent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $array['amount'] * 100, // Amount in cents
        'currency' => 'usd',
        'payment_method_types' => ['card'],
    ]);

    // Create a new payment record in the database
    $payment = Payment::create([
        'union' => $array['union'] ?? 'no union',
        'trxId' => $paymentIntent->id, // Use Stripe payment_intent as trxId
        'userid' => $array['userid'],
        'type' => 'stripe',
        'amount' => $array['amount'],
        'applicant_mobile' => $array['applicant_mobile'],
        'status' => 'pending', // Payment is pending at this point
        'date' => now(),
        'month' => now()->month,
        'year' => now()->year,
        'paymentUrl' => '', // Will update after session creation
        'ipnResponse' => null,
        'method' => 'card',
        'payment_type' => 'online',
        'balance' => $array['balance'] ?? 0, // Balance is optional
    ]);

    // Create Stripe checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Payment for ' . ($array['union'] ?? 'no union'),
                ],
                'unit_amount' => $array['amount'] * 100, // Amount in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'payment_intent_data' => [
            'capture_method' => 'automatic', // Adjust capture method if necessary
        ],
        'client_reference_id' => $payment->trxId, // Set client reference id to trxId or other unique identifier
        'success_url' => $array['success_url'] . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $array['cancel_url'] . '?session_id={CHECKOUT_SESSION_ID}',
    ]);

    Log::info("session:".$session);

    // Update payment record with Stripe URL and CHECKOUT_SESSION_ID
    $payment->update([
        'paymentUrl' => $session->url,
        'checkout_session_id' => $session->id, // Save session ID to payment model
    ]);

    // Redirect the user to Stripe checkout
    return $session->url;
}



