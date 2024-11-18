<?php

namespace App\Http\Controllers\Auth\admins;

use App\Models\Admin;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminAuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $admin = new Admin([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $admin->save();
        return response()->json([
            'success' => true,
            'message' => 'Admin registered successfully.',
            'data' => $admin,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();
            $token = JWTAuth::fromUser($admin);

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful. Token generated.',
                'token' => $token,
                'data' => $admin,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access. Please check your credentials.',
            'error' => 'Unauthorized',
        ], 401);
    }

    public function checkTokenExpiration(Request $request)
    {
        $token = $request->bearerToken();

        try {
            $user = Auth::guard('admin')->setToken($token)->authenticate();
            $isExpired = JWTAuth::setToken($token)->checkOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Token is valid.',
                'data' => $user,
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired.',
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token.',
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while processing token.',
            ], 500);
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
                    'message' => 'Logged out successfully.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token.',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while processing token.',
            ], 500);
        }
    }

    public function checkToken(Request $request)
    {
        $admin = $request->user('admin');
        if ($admin) {
            return response()->json([
                'success' => true,
                'message' => 'Token is valid.',
                'data' => $admin,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Invalid token.',
                'error' => 'Unauthorized',
            ], 401);
        }
    }


    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $admin = Auth::guard('admin')->user();

        // Check if the current password matches
        if (!Hash::check($request->input('current_password'), $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 400);
        }

        // Update the password
        $admin->password = Hash::make($request->input('new_password'));
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ], 200);
    }

}
