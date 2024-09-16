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
        $filters = $request->all();  // Get all the filters from the request
    
        $userId = auth()->id();  // Get the ID of the currently logged-in user
    
        // Get recently viewed users, sorted by how recently they were viewed, and only active ones
        $recentlyViewedUsers = BrowsingHistory::where('user_id', $userId)
            ->with(['viewedUser' => function ($query) {
                $query->where('status', 'active');  // Fetch only active users
            }])
            ->orderBy('viewed_at', 'desc')
            ->take(10) // Limit to 10 recently viewed users
            ->get()
            ->pluck('viewedUser')  // Extract the users themselves
            ->filter();  // Remove null values if any
    
        // Apply the search filters to fetch more employees
        $query = User::where('role', 'EMPLOYEE')  // EMPLOYER searching for EMPLOYEEs
            ->where('status', 'active');  // Only fetch active users
        $query->filter($filters);  // Apply filters from the request
    
        // Exclude the ones already viewed to avoid recommending the same users
        $query->whereNotIn('id', $recentlyViewedUsers->pluck('id'));
    
        // Check if per_page parameter exists for pagination
        if ($request->has('per_page')) {
            // Paginate based on the per_page parameter
            $perPage = (int) $request->get('per_page');
            $filteredUsers = $query->paginate($perPage);
        }
        // Check if limit parameter exists for limited results
        elseif ($request->has('limit')) {
            // Limit the results based on the limit parameter
            $limit = (int) $request->get('limit');
            $filteredUsers = $query->limit($limit)->get();
        }
        // Default to fetching 4 items if neither per_page nor limit is specified
        else {
            $filteredUsers = $query->limit(4)->get();
        }
    
        // Combine both recently viewed and filtered users into one list
        $finalRecommendations = $recentlyViewedUsers->merge($filteredUsers);
    
        // If the combined list is empty, return an empty array instead of null
        if ($finalRecommendations->isEmpty()) {
            $finalRecommendations = [];
        }
    
        // Send the combined list back to the client
        return response()->json([
            'success' => true,
            'message' => 'Recommended users based on your browsing history and filters applied!',
            'data' => $finalRecommendations,
        ]);
    }
    



}
