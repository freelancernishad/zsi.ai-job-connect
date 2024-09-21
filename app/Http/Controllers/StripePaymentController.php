<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Facades\Log;

class StripePaymentController extends Controller
{
    /**
     * Create a Stripe Payment and save it to the database.
     */
    public function createPayment(Request $request)
    {
        $paymentData = [
            'userid' => 1,
            'amount' => 500,
            'applicant_mobile' => '1234567890',
            'balance' => 100
        ];

        return stripe($paymentData);
    }


    /**
     * Handle payment success (after redirect from Stripe).
     */
    public function paymentSuccess(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Retrieve the session ID from the URL
        $session_id = $request->input('session_id');

        // Retrieve session details from Stripe
        $session = StripeSession::retrieve($session_id);

        // Find the payment by transaction ID or other unique identifier
        $payment = Payment::where('trxId', $session->client_reference_id)->first();

        // Use the private function to update payment status
        $this->updatePaymentStatus($payment, $session);

        // Redirect the user to a success page or show success message
        return view('payment.success', ['payment' => $payment]);
    }

    /**
     * Handle Stripe webhook notifications.
     */
    public function handleWebhook(Request $request)
    {
        // Set your Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Get the webhook secret from the environment variables
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        // Get the payload and signature header from the request
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        // Log the payload for debugging purposes
        Log::info('Webhook Payload: ', ['payload' => $payload]);

        try {
            // Verify the event with the Stripe webhook secret
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

            // Log the event type for debugging purposes
            Log::info('Stripe Event Type: ', ['event_type' => $event->type]);

            // Handle the 'checkout.session.completed' event
            if ($event->type == 'checkout.session.completed') {
                $session = $event->data->object;

                // Log the session data for debugging purposes
                Log::info('Session Object: ', ['session' => $session]);

                // Find the payment by the session's client reference ID (trxId)
                $payment = Payment::where('trxId', $session->client_reference_id)->first();

                // Check if the payment exists
                if ($payment) {
                    // Use the private function to update the payment status
                    $this->updatePaymentStatus($payment, $session);

                    // Log the successful processing of the payment
                    Log::info('Payment processed successfully: ', ['payment' => $payment]);
                } else {
                    // Log if the payment is not found
                    Log::warning('Payment not found for trxId: ' . $session->client_reference_id);
                }
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
            // General exception handling
            Log::error('Webhook Error: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Webhook Error'], 400);
        }

        // Return a success response to acknowledge receipt of the webhook
        return response()->json(['status' => 'Webhook received']);
    }

    /**
     * Private function to update the payment status.
     */
    private function updatePaymentStatus($payment, $session)
    {
        if ($session->payment_status == 'paid') {
            // Update payment to success
            $payment->update([
                'status' => 'approved',
                'ipnResponse' => json_encode($session),
            ]);
        } else {
            // Update payment to failed
            $payment->update([
                'status' => 'failed',
                'ipnResponse' => json_encode($session),
            ]);
        }
    }

    /**
     * Handle payment failure (cancel URL).
     */
    public function paymentFailed()
    {
        // Show the failed payment page or return a failure message
        return view('payment.failed');
    }
}
