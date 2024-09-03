<?php

namespace App\Http\Controllers\Auth\users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{

    public function verifyEmail(Request $request, $hash)
    {
        // Find the user by the hash
        $user = User::where('email_verification_hash', $hash)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification link.',
            ], 400);

        }

        // Check if the email is already verified
        if ($user->hasVerifiedEmail()) {
            // Generate a new token for the user
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Email has already been verified.',
                'user' => [
                    'email' => $user->email,
                    'role' => $user->role,
                    'username' => $user->username,
                    'step' => $user->step,
                    'verified' => true, // Email is verified
                ],
                'token' => $token // Return the new token
            ], 200);

        }

        // If not verified, verify the user's email
        $user->markEmailAsVerified();

        // Generate a new token for the user after verification
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'user' => [
                'email' => $user->email,
                'role' => $user->role,
                'username' => $user->username,
                'step' => $user->step,
                'verified' => true, // Email is now verified
            ],
            'token' => $token // Return the new token
        ], 200);

    }



}
