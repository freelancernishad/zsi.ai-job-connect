<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HiringRequest;
use App\Models\HiringSelection;
use App\Models\HiringAssignment;
use App\Models\User; // Assuming Employee is also a User
use App\Models\Admin; // Ensure this model exists

class HiringProcessController extends Controller
{
    /**
     * Create a new hiring request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createHiringRequest(Request $request)
    {
        $request->validate([
            'employer_id' => 'required|exists:users,id',
            'job_title' => 'required|string|max:255',
            'job_description' => 'required|string',
            'expected_start_date' => 'required|date',
            'salary_offer' => 'required|numeric',
            'selected_employees' => 'required|array',
            'selected_employees.*' => 'required|exists:users,id',
        ]);

        $hiringRequest = HiringRequest::create([
            'employer_id' => $request->input('employer_id'),
            'job_title' => $request->input('job_title'),
            'job_description' => $request->input('job_description'),
            'expected_start_date' => $request->input('expected_start_date'),
            'salary_offer' => $request->input('salary_offer'),
            'status' => 'Pending',
        ]);

        foreach ($request->input('selected_employees') as $employeeId) {
            HiringSelection::create([
                'hiring_request_id' => $hiringRequest->id,
                'employee_id' => $employeeId,
                'selection_note' => null, // Or provide a default note if needed
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hiring request created successfully. Your job posting is now live, and selected employees have been notified.',
            'hiring_request' => $hiringRequest,
        ], 201);
    }

    /**
     * Assign an employee to a hiring request.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignEmployee(Request $request, $id)
    {
        $request->validate([
            'assigned_employee_id' => 'required|exists:users,id',
            'assignment_note' => 'nullable|string',
            'assignment_date' => 'required|date',
        ]);

        $hiringRequest = HiringRequest::findOrFail($id);

        // Ensure only one employee is assigned per hiring request
        $existingAssignment = HiringAssignment::where('hiring_request_id', $id)->first();
        if ($existingAssignment) {
            return response()->json([
                'success' => false,
                'message' => 'An employee has already been assigned to this hiring request. Please review the assignment.',
            ], 400);
        }

        $assignment = HiringAssignment::create([
            'hiring_request_id' => $id,
            'assigned_employee_id' => $request->input('assigned_employee_id'),
            'admin_id' => $request->user()->id, // Assuming admin is the current authenticated user
            'assignment_note' => $request->input('assignment_note'),
            'assignment_date' => $request->input('assignment_date'),
            'status' => 'Assigned',
        ]);

        // Update the status of the hiring request
        $hiringRequest->update(['status' => 'Assigned']);

        return response()->json([
            'success' => true,
            'message' => 'Employee successfully assigned to the hiring request. The assignment has been recorded.',
            'assignment' => $assignment,
        ], 200);
    }

    /**
     * Get details of a hiring request including assigned employee.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHiringRequest($id)
    {
        $hiringRequest = HiringRequest::with(['selectedEmployees.employee', 'hiringAssignments.employee'])
                                      ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Hiring request details retrieved successfully.',
            'hiring_request' => [
                'id' => $hiringRequest->id,
                'employer_id' => $hiringRequest->employer_id,
                'job_title' => $hiringRequest->job_title,
                'job_description' => $hiringRequest->job_description,
                'expected_start_date' => $hiringRequest->expected_start_date,
                'salary_offer' => $hiringRequest->salary_offer,
                'status' => $hiringRequest->status,
                'selected_employees' => $hiringRequest->selectedEmployees->map(function ($selection) {
                    return $selection->employee;
                }),
                'assigned_employee' => $hiringRequest->hiringAssignments->first()?->employee ?: null,
            ],
        ], 200);
    }

    /**
     * Get all hiring requests by a specific step.
     *
     * @param string $step
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestsByStep($step)
    {
        $requests = HiringRequest::where('status', $step)->get();

        return response()->json([
            'success' => true,
            'message' => "Hiring requests at the '$step' step retrieved successfully.",
            'requests' => $requests,
        ], 200);
    }

    /**
     * Get all hiring requests (for admin) with pagination or filtering if needed.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRequests()
    {
        $requests = HiringRequest::all(); // or use paginate() for pagination

        return response()->json([
            'success' => true,
            'message' => 'All hiring requests retrieved successfully.',
            'requests' => $requests,
        ], 200);
    }

    /**
     * Get all hiring requests for a specific employer.
     *
     * @param int $employerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestsByEmployer($employerId)
    {
        $requests = HiringRequest::where('employer_id', $employerId)->get();

        return response()->json([
            'success' => true,
            'message' => "Hiring requests for employer ID $employerId retrieved successfully.",
            'employer_id' => $employerId,
            'requests' => $requests,
        ], 200);
    }

    /**
     * Get all hiring requests by their step with pagination.
     *
     * @param string $step
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestsByStepWithPagination($step)
    {
        $requests = HiringRequest::where('status', $step)->paginate(10); // Adjust pagination as needed

        return response()->json([
            'success' => true,
            'message' => "Hiring requests at the '$step' step retrieved successfully with pagination.",
            'step' => $step,
            'requests' => $requests,
        ], 200);
    }
}
