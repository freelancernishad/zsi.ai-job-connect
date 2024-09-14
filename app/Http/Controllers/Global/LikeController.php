<?php

// app/Http/Controllers/Global/LikeController.php

namespace App\Http\Controllers\Global;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\User;

class LikeController extends Controller
{
    // Like a user
    public function likeUser(Request $request)
    {
        // Validate the request
        $request->validate([
            'liked_user_id' => 'required|exists:users,id',
        ]);

        $userId = auth()->id();  // Get the ID of the currently logged-in user
        $likedUserId = $request->input('liked_user_id');

        // Check if the user has already liked the other user
        $existingLike = Like::where('user_id', $userId)
                            ->where('liked_user_id', $likedUserId)
                            ->first();

        if ($existingLike) {
            return response()->json([
                'success' => false,
                'message' => 'User already liked.',
            ], 400);
        }

        // Create a new like record
        Like::create([
            'user_id' => $userId,
            'liked_user_id' => $likedUserId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User liked successfully.',
        ], 200);
    }

    // Get liked users for the current user
    public function getLikedUsers()
    {
        $userId = auth()->id();  // Get the ID of the currently logged-in user

        // Fetch users who have liked this user
        $likedUsers = Like::where('user_id', $userId)
                          ->with('likedUser') // Load the liked user
                          ->get()
                          ->pluck('likedUser');

        return response()->json([
            'success' => true,
            'message' => 'Fetched liked users successfully.',
            'data' => $likedUsers,
        ], 200);
    }
}
