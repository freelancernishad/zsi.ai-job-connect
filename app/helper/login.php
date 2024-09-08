<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

function handleGoogleLogin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'access_token' => 'required|string',
        'role' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        // Fetch user data from Google API
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'access_token' => $request->access_token,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid access token.',
            ], 400);
        }

        $userData = $response->json();
        $user = User::where('email', $userData['email'])->first();

        $employerStep = $request->role === 'EMPLOYER' ? 2 : 0;
        $status = $request->role === 'EMPLOYEE' ? 'inactive' : 'active';

        if ($user) {
            if ($user->employer_step == 2 && $user->step !== 3) {
                if ($request->role === 'EMPLOYEE') {
                    $user->update([
                        'step' => 1,
                        'status' => 'inactive',
                    ]);
                } elseif ($request->role === 'EMPLOYER') {
                    $user->update([
                        'step' => 2,
                        'status' => 'active',
                    ]);
                }
            } else {
                // Update role without changing step and status if not meeting above conditions
                $user->update([
                    'role' => $request->role,
                ]);
            }
        } else {
            // Create a new user
            $user = User::create([
                'username' => explode('@', $userData['email'])[0],
                'email' => $userData['email'],
                'password' => Hash::make(Str::random(16)), // Generate a random password
                'role' => $request->role,
                'step' => $employerStep,
                'status' => $status,
                'email_verified_at' => now(),
            ]);
        }

        // Authenticate and respond with token
        Auth::login($user);
        return respondWithToken($user);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'An error occurred during authentication.',
            'details' => $e->getMessage(),
        ], 500);
    }
}



function handleEmailLogin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
        'role' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    if (Auth::attempt($request->only('email', 'password'))) {
        $user = Auth::user();

        if ($user->employer_step == 2 && $user->step !== 3) {
            if ($request->role === 'EMPLOYEE') {
                $user->update([
                    'step' => 1,
                    'status' => 'inactive',
                ]);
            } elseif ($request->role === 'EMPLOYER') {
                $user->update([
                    'step' => 2,
                    'status' => 'active',
                ]);
            }
        } else {
            // Update role without changing step and status if not meeting above conditions
            $user->update([
                'role' => $request->role,
            ]);
        }

        return respondWithToken($user);
    }

    return response()->json([
        'success' => false,
        'message' => 'Invalid credentials. Please check your email and password.',
    ], 401);
}


 function createUserFromGoogle($userData, $role)
{
    $username = explode('@', $userData['email'])[0];

    return User::create([
        'username' => $username,
        'email' => $userData['email'],
        'password' => Hash::make(Str::random(16)),
        'role' => $role,
        'step' => 1,
        'email_verified_at' => now(),
    ]);
}

 function respondWithToken(User $user)
{
    $payload = [
        'email' => $user->email,
        'role' => $user->role,
        'username' => $user->username,
        'step' => $user->step,
        'verified' => $user->hasVerifiedEmail(),
        'activation_payment_made' => $user->activation_payment_made,
    ];

    $token = JWTAuth::fromUser($user, ['guard' => 'user']);

    return response()->json([
        'success' => true,
        'message' => 'Authentication successful.',
        'token' => $token,
        'user' => $payload,
    ], 200);
}
