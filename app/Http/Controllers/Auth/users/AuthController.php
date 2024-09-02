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
        // Check if access_token is provided
        if ($request->has('access_token')) {
            // Validate access_token and role
            $validator = Validator::make($request->all(), [
                'access_token' => 'required|string',
                'role' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Verify the access token using Google's tokeninfo endpoint
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'access_token' => $request->access_token,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Invalid access token'], 400);
            }

            $userData = $response->json();

            // Check if the email exists in your database
            $user = User::where('email', $userData['email'])->first();
            if (!$user) {
                // If user does not exist, create a new user
                $username = explode('@', $userData['email'])[0]; // Extract username from email
                $user = User::create([
                    'username' => $username,
                    'email' => $userData['email'],
                    'password' => Hash::make(Str::random(16)), // Generate a random password
                    'role' => $request->role,
                    'step' => 1, // Set step value to 1
                ]);
            }

            // Login the user
            Auth::login($user);

            // Build the payload including the username and step
            $payload = [
                'email' => $user->email,
                'role' => $user->role,
                'username' => $user->username, // Include username here
                'step' => $user->step, // Include step here
                'verified' => $user->hasVerifiedEmail(), // Add email verification status
            ];

            $token = JWTAuth::fromUser($user, ['guard' => 'user']);
            return response()->json(['token' => $token, 'user' => $payload], 200);
        } else {
            // Validate email and password
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
            ];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                // Build the payload including the username and step
                $payload = [
                    'email' => $user->email,
                    'role' => $user->role,
                    'username' => $user->username, // Include username here
                    'step' => $user->step, // Include step here
                    'verified' => $user->hasVerifiedEmail(), // Add email verification status
                ];

                $token = JWTAuth::fromUser($user, ['guard' => 'user']);
                return response()->json(['token' => $token, 'user' => $payload], 200);
            }

            return response()->json(['message' => 'Invalid credentials'], 401);
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
            return response()->json(['message' => 'Email is either already verified or user does not exist.'], 400);
        }

        // Generate a new verification token
        $verificationToken = Str::random(60); // Generate a unique token
        $user->email_verification_hash = $verificationToken;
        $user->save();

        // Build the new verification URL
        $verify_url = $request->verify_url;

        // Resend the verification email
        $user->notify(new VerifyEmail($user, $verify_url));

        return response()->json(['message' => 'Verification link has been sent.'], 200);
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
        return response()->json(['message' => 'Token is valid', 'user' => $user ], 200);
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        // Token has expired
        return response()->json(['message' => 'Token has expired'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        // Token is invalid
        return response()->json(['message' => 'Invalid token'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        // Token not found or other JWT exception
        return response()->json(['message' => 'Error while processing token'], 500);
    }
}
public function checkToken(Request $request)
{
    $user = $request->user('web');
    if ($user) {
        return response()->json(['message' => 'Token is valid']);
    } else {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if ($token) {
                JWTAuth::setToken($token)->invalidate();
                return response()->json(['message' => 'Logged out successfully'], 200);
            } else {
                return response()->json(['message' => 'Invalid token'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Error while processing token'], 500);
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
                return response()->json(['error' => 'Invalid access token'], 400);
            }

            $userData = $response->json();

            // Check if the email already exists
            $existingUser = User::where('email', $userData['email'])->first();
            if ($existingUser) {
                return response()->json(['error' => 'Email already registered'], 400);
            }

            // Extract username from email
            $username = explode('@', $userData['email'])[0];

            // Create a new user
            $user = new User([
                'username' => $username,
                'email' => $userData['email'],
                'password' => Hash::make(Str::random(16)), // Generate a random password since it's not provided
                'role' => $request->role,
                'step' => 1, // Set step value to 1
            ]);

            $user->save();
        } else {
            // Validate email, password, and role
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

            // Create a new user
            $user = new User([
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'step' => 1, // Set step value to 1
                'email_verification_hash' => Str::random(60),
            ]);

            $user->save();


            // Generate verification URL
            $verify_url = $request->verify_url;

            // Send email verification
            $user->notify(new VerifyEmail($user, $verify_url));


        }





        // Build the payload including the username and step
        $payload = [
            'email' => $user->email,
            'role' => $user->role,
            'username' => $user->username, // Include username here
            'step' => $user->step, // Include step here
            'verified' => $user->hasVerifiedEmail(), // Add email verification status
        ];

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        // Return the response with token and user data
        return response()->json(['token' => $token, 'user' => $payload], 201);
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
                return response()->json(['message' => 'Current password is incorrect.'], 400);
             }
             $user->password = Hash::make($request->new_password);
             $user->save();
             return response()->json(['message' => 'Password changed successfully.'], 200);
         }


}
