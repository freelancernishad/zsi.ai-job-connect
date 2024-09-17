<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeHiringPrice;
use Illuminate\Http\Request;

class EmployeeHiringPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Fetch all pricing rules
        $prices = EmployeeHiringPrice::orderBy('number_of_employees','asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Employee hiring prices retrieved successfully.',
            'data' => $prices,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'number_of_employees' => 'required|integer|min:1',
            'price_per_employee' => 'required|numeric',
        ]);

        // Calculate total price
        $totalPrice = $validated['number_of_employees'] * $validated['price_per_employee'];

        // Create a new price rule
        $price = EmployeeHiringPrice::create([
            'number_of_employees' => $validated['number_of_employees'],
            'price_per_employee' => $validated['price_per_employee'],
            'total_price' => $totalPrice,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee hiring price created successfully.',
            'data' => $price,
        ], 201); // 201 = Created
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeeHiringPrice  $employeeHiringPrice
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(EmployeeHiringPrice $employeeHiringPrice)
    {
        return response()->json([
            'success' => true,
            'message' => 'Employee hiring price retrieved successfully.',
            'data' => $employeeHiringPrice,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmployeeHiringPrice  $employeeHiringPrice
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, EmployeeHiringPrice $employeeHiringPrice)
    {
        // Validate input
        $validated = $request->validate([
            'number_of_employees' => 'required|integer|min:1',
            'price_per_employee' => 'required|numeric',
        ]);

        // Calculate total price
        $totalPrice = $validated['number_of_employees'] * $validated['price_per_employee'];

        // Update the price rule
        $employeeHiringPrice->update([
            'number_of_employees' => $validated['number_of_employees'],
            'price_per_employee' => $validated['price_per_employee'],
            'total_price' => $totalPrice,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee hiring price updated successfully.',
            'data' => $employeeHiringPrice,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeHiringPrice  $employeeHiringPrice
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(EmployeeHiringPrice $employeeHiringPrice)
    {
        $employeeHiringPrice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee hiring price deleted successfully.',
            'data' => null,
        ], 204); // 204 = No Content
    }
}
