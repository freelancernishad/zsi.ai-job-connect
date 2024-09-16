<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BrowsingHistory;
use App\Models\Service;

class BrowsingHistoryController extends Controller
{
    public function recommendUsersWithFilters(Request $request)
    {
        $userId = auth()->id();  // Get the ID of the currently logged-in user

        // Get recently viewed users by this user, sorted by how recently they were viewed, and only active ones
        $recentlyViewedUsers = BrowsingHistory::where('user_id', $userId)
            ->with(['viewedUser' => function ($query) {
                $query->where('status', 'active');  // Fetch only active users
            }])
            ->orderBy('viewed_at', 'desc')
            ->take(10)  // Limit to 10 recently viewed users
            ->get()
            ->pluck('viewedUser')  // Extract the users themselves
            ->filter();  // Remove null values (in case some browsing history entries have no user linked)

        // If pagination is requested, apply it to the recently viewed users
        if ($request->has('per_page')) {
            $perPage = (int) $request->get('per_page');
            $finalRecommendations = $recentlyViewedUsers->forPage(1, $perPage);  // Paginate the collection manually
        }
        // If limit is requested, limit the number of results
        elseif ($request->has('limit')) {
            $limit = (int) $request->get('limit');
            $finalRecommendations = $recentlyViewedUsers->take($limit);
        }
        // Default to fetching all recently viewed users (with a maximum limit)
        else {
            $finalRecommendations = $recentlyViewedUsers->take(4);  // Default limit of 4
        }



        // Return the recommendations or an empty array if no users are found
        return response()->json([
            'success' => true,
            'message' => 'Recommended users based on your browsing history!',
            'data' => $finalRecommendations->isNotEmpty() ? $finalRecommendations : getRandomActiveUsers(),
        ]);
    }




}
