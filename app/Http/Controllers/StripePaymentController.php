<?php
namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;


class StripePaymentController extends Controller
{




    public function showPaymentForm()
    {
        return view('payment');
    }

    public function createPayment(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Product Name',
                    ],
                    'unit_amount' => 2000,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost:8000/success',
            'cancel_url' => 'http://localhost:8000/failed',
        ]);

        return $session->url;
    }




    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            // Log the payload for debugging
            Log::info('Stripe Webhook Received: ', ['payload' => $payload]);

            // Verify the event by comparing its signature to your webhook secret
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

            // Log event type
            Log::info('Stripe Event Type: ', ['event_type' => $event->type]);

            // Handle different event types
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                // Log session details
                Log::info('Checkout Session Completed: ', [
                    'session_id' => $session->id,
                    'payment_status' => $session->payment_status,
                    'customer_email' => $session->customer_details->email,
                    'amount_total' => $session->amount_total / 100
                ]);

                // You can get the payment information here
                $payment_status = $session->payment_status; // Example: "paid"
                $customer_email = $session->customer_details->email;
                $amount_total = $session->amount_total / 100; // Amount in dollars

                // Handle successful payment (e.g., update order status, send receipt, etc.)
            }

        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Invalid Payload: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid Payload'], 400);

        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Invalid Signature: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid Signature'], 400);

        } catch (\Exception $e) {
            // General exception
            Log::error('Webhook Error: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Webhook Error'], 400);
        }

        // Log success response
        Log::info('Webhook processed successfully');
        return response()->json(['status' => 'Webhook received']);
    }





}
