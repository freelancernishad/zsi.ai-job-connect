<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    /**
     * Get all users who have made payments for activation.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersWithPendingPayments()
    {
        $payments = Payment::where('type', 'activation')
                            ->where('status', 'pending')
                            ->with('user') // Assuming you have a relationship set up
                            ->get();

        // Extract user details from payments
        $users = $payments->map(function ($payment) {
            return $payment->user; // Assuming 'user' is a relation on Payment model
        });

        return response()->json([
            'success' => true,
            'message' => 'Users with pending payments retrieved successfully.',
            'data' => $payments,
        ]);
    }

    /**
     * Approve payment and activate the user.
     *
     * @param int $paymentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvePayment($paymentId)
    {
        $payment = Payment::find($paymentId);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'The specified payment could not be found. Please check the payment ID and try again.',
            ], 404);
        }

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'The payment is not currently in a pending status. Please check the payment status and try again.',
            ], 400);
        }

        // Update payment status to approved
        $payment->update(['status' => 'approved']);

        // Activate the user
        $user = User::find($payment->userid);
        if ($user) {
            $user->update([
                'status' => 'active',
                'step' => 3,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment has been approved, and the user has been activated successfully.',
        ]);

    }

    public function cancelPayment($paymentId)
    {
        // Find the payment by ID
        $payment = Payment::find($paymentId);

        // Check if the payment exists
        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'The specified payment could not be found. Please check the payment ID and try again.',
            ], 404);
        }

        // Check if the payment status is not already canceled
        if ($payment->status === 'canceled') {
            return response()->json([
                'success' => false,
                'message' => 'The payment is already canceled.',
            ], 400);
        }

        // Check if the payment status is not pending or approved
        if ($payment->status !== 'pending' && $payment->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'The payment is not in a status that can be canceled. Please check the payment status and try again.',
            ], 400);
        }

        // Update payment status to canceled
        $payment->update(['status' => 'canceled']);

        // Optionally, you can deactivate the user or perform other actions if needed
        // For example, if the payment is associated with a user:
        $user = User::find($payment->userid);
        if ($user) {
            $user->update([
                'status' => 'inactive',
                'step' => 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment has been canceled successfully.',
        ]);
    }

}
