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
    public function getUsersWithPendingPayments(Request $request)
    {
        // Get the search parameter from the request, if it exists
        $search = $request->input('search');

        // Initialize a search variable for use in the query
        $searchTerm = null;

        // Set search term if it exists
        if (!empty($search)) {
            $searchTerm = trim($search); // Clean the search input
        }

        // Query payments with pending status and type 'activation'
        $payments = Payment::where('type', 'activation')
                            ->where('status', 'pending')
                            ->whereHas('user', function ($query) use ($searchTerm) {
                                // Apply search filter if it exists, looking in both name and email
                                if (!empty($searchTerm)) {
                                    $query->where(function ($query) use ($searchTerm) {
                                        $query->where('name', 'like', '%' . $searchTerm . '%')
                                              ->orWhere('email', 'like', '%' . $searchTerm . '%');
                                    });
                                }
                            })
                            ->with('user') // Assuming the relationship is set up
                            ->orderBy('created_at', 'desc') // Sort by latest to oldest
                            ->get();

        // Extract user details from payments
        // $users = $payments->map(function ($payment) {
        //     return $payment; // Assuming 'user' is a relation on Payment model
        // });

        return response()->json([
            'success' => true,
            'message' => 'Users with pending payments retrieved successfully.',
            'data' => $payments, // Returning the users
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


    public function getUsersBySearch(Request $request)
    {
        $searchTerm = $request->query('search');
        $role = $request->query('role', 'employee'); // Default to 'employee'
        $perPage = $request->query('per_page', 15); // Default per_page to 15

        // Build the query
        $query = User::query();

        // Global search
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }

        // Check role and apply appropriate status filters
        if ($role == 'EMPLOYEE') {
            $query->where('status', 'active')
            ->where('role', $role); // Only show active employees

            $relationships = [
                'languages',
                'certifications',
                'skills',
                'education',
                'employmentHistory',
                'resume',
                'thumbnail'
            ];
        } elseif ($role == 'EMPLOYER') {
            $query->where('employer_status', 'active') // Only show active employers
                  ->where('role', $role); // Ensure only employers are queried

                  $relationships = [
                    'servicesLookingFor',
                ];


        }



        $users = $query->with($relationships)->paginate($perPage);

        return jsonResponse(true, "Users retrieved successfully.", $users);
    }




}
