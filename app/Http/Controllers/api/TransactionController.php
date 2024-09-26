<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    /**
     * Get all approved transactions with optional pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTransactions(Request $request)
    {
        // Determine the number of items per page (default to 10 if not provided)
        $perPage = $request->query('per_page', 10);

        // Paginate the approved transactions and eager load the user and related relationships
        $transactions = Payment::with([
                'user.languages',           // Eager load user's languages
                'user.certifications',      // Eager load user's certifications
                'user.skills',              // Eager load user's skills
                'user.education',           // Eager load user's education
                'user.employmentHistory',   // Eager load user's employment history
                'user.resume',              // Eager load user's resume
                'hiringRequest'             // Eager load hiring request related to transaction
            ])
            ->where('status', 'approved') // Filter by approved status
            ->orderBy('id', 'desc') // Sort by ID in descending order
            ->paginate($perPage);

        // Return response using the jsonResponse function
        if ($transactions->isEmpty()) {
            return jsonResponse(false, 'No approved transactions found.', [], 404);
        }

        return jsonResponse(true, 'Approved transactions retrieved successfully.', $transactions);
    }

    /**
     * Get approved transactions filtered by type with default 'Hiring-Request', with optional pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionsByType(Request $request)
    {
        // Get the type from the query parameter, default to 'Hiring-Request'
        $type = $request->query('type', 'Hiring-Request');

        // Determine the number of items per page (default to 10 if not provided)
        $perPage = $request->query('per_page', 10);

        // Fetch approved transactions based on the type, eager load relations, and paginate them
        $transactions = Payment::with([
                'user.languages',           // Eager load user's languages
                'user.certifications',      // Eager load user's certifications
                'user.skills',              // Eager load user's skills
                'user.education',           // Eager load user's education
                'user.employmentHistory',   // Eager load user's employment history
                'user.resume',              // Eager load user's resume
                'hiringRequest'             // Eager load hiring request related to transaction
            ])
            ->where('status', 'approved') // Filter by approved status
            ->where('type', $type)
            ->orderBy('id', 'desc') // Sort by ID in descending order
            ->paginate($perPage);

        // Return response using the jsonResponse function
        if ($transactions->isEmpty()) {
            return jsonResponse(false, "No approved transactions found for type: $type.", [], 404);
        }

        return jsonResponse(true, "Approved transactions for type: $type retrieved successfully.", $transactions);
    }

    /**
     * Get approved transactions filtered by user ID with optional pagination
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionsByUser(Request $request, $userId)
    {
        // Determine the number of items per page (default to 10 if not provided)
        $perPage = $request->query('per_page', 10);

        // Fetch approved transactions for a specific user, eager load relations, and paginate them
        $transactions = Payment::with([
                'user.languages',           // Eager load user's languages
                'user.certifications',      // Eager load user's certifications
                'user.skills',              // Eager load user's skills
                'user.education',           // Eager load user's education
                'user.employmentHistory',   // Eager load user's employment history
                'user.resume',              // Eager load user's resume
                'hiringRequest'             // Eager load hiring request related to transaction
            ])
            ->where('status', 'approved') // Filter by approved status
            ->where('userid', $userId)
            ->orderBy('id', 'desc') // Sort by ID in descending order
            ->paginate($perPage);

        // Return response using the jsonResponse function
        if ($transactions->isEmpty()) {
            return jsonResponse(false, "No approved transactions found for user ID: $userId.", [], 404);
        }

        return jsonResponse(true, "Approved transactions for user ID: $userId retrieved successfully.", $transactions);
    }
}
