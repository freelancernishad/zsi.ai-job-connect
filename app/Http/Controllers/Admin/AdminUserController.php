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

        // Check if the payment status is already canceled
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

        // Find the associated user
        $user = User::find($payment->userid);

        // If the user exists and their status is 'active'
        if ($user && $user->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'The payment cannot be canceled because the user is already active. Once a user is activated, canceling the payment is not allowed.',
            ], 400);
        }

        // Update payment status to canceled
        $payment->update(['status' => 'canceled']);

        // If the user exists and is not active, update the user's status
        if ($user) {
            $user->update([
                'status' => 'inactive',
                'activation_payment_made' => false,
                'activation_payment_cancel' => true,
                // 'step' => 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment has been canceled successfully.',
        ]);
    }



    public function getUsersByRole(Request $request)
    {
        // Get the search parameter for global search
        $searchQuery = $request->query('search'); // This will be the search term (name, email, phone_number)

        // Get the role parameter (optional)
        $role = $request->query('role'); // Get the role parameter

        // Get the per_page parameter with a default of 10
        $perPage = $request->query('per_page', 10);

        // Start the query to retrieve users
        $query = User::query();

        // Apply global search filters if a search term is provided
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('email', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('phone_number', 'LIKE', '%' . $searchQuery . '%');
            });
        }

        // Apply role-based filters if a role is provided
        if ($role) {


            // if ($role === 'EMPLOYEE') {
            //     $query->where('role', 'EMPLOYEE')->where('status', 'active');
            // } elseif ($role === 'EMPLOYER') {
            //     $query->where('role', 'EMPLOYER')->where('employer_status', 'active');
            // }


            if ($role === 'EMPLOYEE') {
                $query->where('role', 'EMPLOYEE');
            } elseif ($role === 'EMPLOYER') {
                $query->where('role', 'EMPLOYER');
            }



        } else {
            // If no role is specified, filter by active employees and employers
            $query->where(function($q) {
                $q->where(function($subQuery) {
                    $subQuery->where('status', 'active')
                             ->where('employer_status', 'inactive');
                })->orWhere(function($subQuery) {
                    $subQuery->where('status', 'inactive')
                             ->where('employer_status', 'active');
                })->orWhere(function($subQuery) {
                    $subQuery->where('status', 'active')
                             ->where('employer_status', 'active');
                });
            });
        }


        // Get the service parameter for preferred job title filter (optional)
        $service = $request->query('service'); // This can be 1, 2, or 3
        // Apply preferred_job_title filter if service is provided
        if ($service) {
            $query->where('preferred_job_title', $service);
        }


        // Retrieve the users with eager loading and pagination
        $users = $query->with([
            'languages',
            'certifications',
            'skills',
            'education',
            'employmentHistory',
            'resume',
            'thumbnail',
            'servicesLookingFor'
        ])->paginate($perPage);

        // Build response using jsonResponse
        return jsonResponse(true, 'Users retrieved successfully.', $users);
    }














}
