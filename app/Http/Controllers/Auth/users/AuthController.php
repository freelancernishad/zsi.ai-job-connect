<?php
// namespace App\Http\Controllers\Auth;
namespace App\Http\Controllers\Auth\users;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Notifications\VerifyEmail;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->has('access_token')) {
            return handleGoogleLogin($request);
        } else {
            return handleEmailLogin($request);
        }
    }


    public function resendVerificationLink(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            // Optionally validate verify_url if it's part of the request
            'verify_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists and if the email is not already verified
        if (!$user || $user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'The email is either already verified or the user does not exist.',
            ], 400);
        }

        // Generate a new verification token
        $verificationToken = Str::random(60); // Generate a unique token
        $user->email_verification_hash = $verificationToken;
        $user->save();





        // Build the new verification URL
        // $verify_url = $request->verify_url;

        // Resend the verification email
        // $user->notify(new VerifyEmail($user, $verify_url));




        try {
            $verify_url = $request->verify_url;
            $user->notify(new VerifyEmail($user, $verify_url));
        } catch (\Exception $e) {
            // If email sending fails, the process will continue without any error
        }





        return response()->json([
            'success' => true,
            'message' => 'Verification link has been sent to your email address.',
        ], 200);

    }



public function checkTokenExpiration(Request $request)
{


    // return $token = $request->token;
     $token = $request->bearerToken();


    try {

        $payload = JWTAuth::setToken($token)->getPayload();

        // Check if the token's expiration time (exp) is greater than the current timestamp
        $isExpired = $payload->get('exp') < time();

        $user = Auth::guard('web')->setToken($token)->authenticate();


        // Get user's roles
    //   $roles = $user->roles;
    // return $roles->permissions;

    // Initialize an empty array to store permissions
    // $permissions = [];

    // Loop through each role to fetch permissions
    // foreach ($roles as $role) {
        // Merge permissions associated with the current role into the permissions array
        // $permissions = array_merge($permissions, $roles->permissions->toArray());
    // }

    // Remove duplicates and re-index the array
    // $permissions = array_values(array_unique($permissions, SORT_REGULAR));

    // Now $permissions contains all unique permissions associated with the user
    // You can use $permissions as needed

        // $user = JWTAuth::setToken($token)->authenticate();
        return response()->json([
            'success' => true,
            'message' => 'Token is valid.',
            'user' => $user
        ], 200);

    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        // Token has expired
        return response()->json([
            'success' => false,
            'message' => 'Token has expired. Please log in again.'
        ], 401);

    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        // Token is invalid
        return response()->json([
            'success' => false,
            'message' => 'Invalid token. Please check your authentication details and try again.'
        ], 401);

    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        // Token not found or other JWT exception
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while processing the token. Please try again later.'
        ], 500);

    }
}
public function checkToken(Request $request)
{
    $user = $request->user('web');
    if ($user) {
        return response()->json([
            'success' => true,
            'message' => 'Token is valid',
            'user' => $user // Optionally include user details if relevant
        ], 200);

    } else {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access'
        ], 401);

    }
}
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if ($token) {
                JWTAuth::setToken($token)->invalidate();
                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully'
                ], 200);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token'
                ], 401);

            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while processing token'
            ], 500);

        }
    }


    public function register(Request $request)
    {
        if ($request->has('access_token')) {
            // Validate access_token and role
            $validator = Validator::make($request->all(), [
                'access_token' => 'required|string',
                'role' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Fetch user info from Google API using the access token
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $request->access_token,
            ])->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid access token'
                ], 400);
            }

            $userData = $response->json();

            // Check if the email already exists
            $existingUser = User::where('email', $userData['email'])->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already registered'
                ], 400);
            }

            // Extract username from email
            $username = explode('@', $userData['email'])[0];

            // Determine the employer_step based on the role
            $employerStep = $request->role === 'EMPLOYER' ? 1 : 0;

            // Both employer_status and status will be inactive initially
            $statusFields = [
                'employer_status' => 'inactive',
                'status' => 'inactive',
            ];

            // Create a new user
            $user = new User(array_merge([
                'username' => $username,
                'email' => $userData['email'],
                'password' => Hash::make(Str::random(16)), // Generate a random password
                'role' => $request->role,
                'step' => 1, // Set step value to 1
                'employer_step' => $employerStep, // Add employer_step here
                'email_verified_at' => now(),
            ], $statusFields));

            $user->save();
        } else {
            // Validate email, password, role, and verify_url
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required',
                'verify_url' => 'required|url', // Ensure verify_url is a valid URL
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Extract username from email
            $username = explode('@', $request->email)[0];

            // Determine the employer_step based on the role
            $employerStep = $request->role === 'EMPLOYER' ? 1 : 0;

            // Both employer_status and status will be inactive initially
            $statusFields = [
                'employer_status' => 'inactive',
                'status' => 'inactive',
            ];

            // Create a new user
            $user = new User(array_merge([
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'step' => 1, // Set step value to 1
                'employer_step' => $employerStep, // Add employer_step here
                'email_verification_hash' => Str::random(60),
            ], $statusFields));

            $user->save();

            // // Generate verification URL
            // $verify_url = $request->verify_url;

            // // Send email verification
            // $user->notify(new VerifyEmail($user, $verify_url));



            try {
                $verify_url = $request->verify_url;
                $user->notify(new VerifyEmail($user, $verify_url));
            } catch (\Exception $e) {
                // If email sending fails, the process will continue without any error
            }


        }

        // Build the payload including the username and step
        $payload = [
            'email' => $user->email,
            'role' => $user->role,
            'username' => $user->username, // Include username here
            'step' => $user->step, // Include step here
            'employer_step' => $user->employer_step, // Include employer_step here
            'employer_status' => $user->employer_status, // Employer status
            'status' => $user->status, // Employee status
            'verified' => $user->hasVerifiedEmail(), // Add email verification status
        ];

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        // Return the response with token and user data
        return response()->json([
            'success' => true,
            'message' => 'Authentication successful. Token and user data returned.',
            'token' => $token,
            'user' => $payload
        ], 201);
    }











         public function changePassword(Request $request)
         {
             $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                 'new_password' => 'required|min:8|confirmed',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $user = Auth::guard('web')->user();
             if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The current password you entered is incorrect. Please try again.'
                ], 400);

             }
             $user->password = Hash::make($request->new_password);
             $user->save();
             return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ], 200);

         }


}
