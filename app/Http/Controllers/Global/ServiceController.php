<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // Get all services
    public function index()
    {
        $services = Service::all();

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved the list of all services.',
            'data' => $services
        ], 200);
    }

    // Get a single service by ID
    public function show($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found. Please check the ID and try again.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved the service details.',
            'data' => $service
        ], 200);
    }
}
