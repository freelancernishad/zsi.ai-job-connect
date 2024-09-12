<?php

namespace App\Http\Controllers\Global;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GlobalUserController extends Controller
{
    /**
     * Filter users based on request query parameters.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterUsers(Request $request)
    {
        // Retrieve all filters from the request
        $filters = $request->all();

        // Start building the query
        $query = User::filter($filters)->with([
            'languages',
            'certifications',
            'skills',
            'education',
            'employmentHistory',
            'preferredJobTitleService'
        ]);
        return $users = $query->paginate(10);

    // Handle preferred_job_title filter
    if ($request->has('preferred_job')) {
        // Get the service name from the request
        $serviceName = $request->get('preferred_job');

        // Get service ID from the service name
        $serviceId = Service::where('name', $serviceName)->pluck('id')->first();

        if ($serviceId) {
            // Filter users by service ID
            $query->where('preferred_job_title', $serviceId);
        } else {
            // If no service found, return empty result
            $query->whereRaw('1 = 0'); // No results
        }
    }

        // Check if per_page parameter exists for pagination
        if ($request->has('per_page')) {
            // Paginate based on the per_page parameter
            $perPage = (int) $request->get('per_page');
            $users = $query->paginate($perPage);
        }
        // Check if limit parameter exists for limited results
        elseif ($request->has('limit')) {
            // Limit the results based on the limit parameter
            $limit = (int) $request->get('limit');
            $users = $query->limit($limit)->get();
        }
        // Default to paginating with 10 items per page
        else {
            $users = $query->paginate(10);
        }

        // Return the filtered and possibly paginated results
        return response()->json($users);
    }

}
