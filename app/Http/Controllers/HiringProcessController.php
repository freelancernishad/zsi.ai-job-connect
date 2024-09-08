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

        return response()->json(['message' => 'Hiring request created successfully'], 201);
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
            return response()->json(['message' => 'Employee already assigned to this request'], 400);
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

        return response()->json(['message' => 'Employee assigned successfully', 'assignment' => $assignment], 200);
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
            'id' => $hiringRequest->id,
            'employer_id' => $hiringRequest->employer_id,
            'job_title' => $hiringRequest->job_title,
            'job_description' => $hiringRequest->job_description,
            'expected_start_date' => $hiringRequest->expected_start_date,
            'salary_offer' => $hiringRequest->salary_offer,
            'status' => $hiringRequest->status,
            'selected_employees' => $hiringRequest->selectedEmployees->map(function ($selection) {

                return $selection->employee;

                // return [
                //     'id' => $selection->employee_id,
                //     'name' => $selection->employee->name,
                //     'email' => $selection->employee->email,
                //     'phone_number' => $selection->employee->phone_number,
                //     'address' => $selection->employee->address,
                //     'date_of_birth' => $selection->employee->date_of_birth,
                //     // Add other fields as needed
                // ];


            }),
            'assigned_employee' => $hiringRequest->hiringAssignments->first()?->employee
                ?
                $hiringRequest->hiringAssignments->first()->employee
                // [
                //     'id' => $hiringRequest->hiringAssignments->first()->employee->id,
                //     'name' => $hiringRequest->hiringAssignments->first()->employee->name,
                //     'email' => $hiringRequest->hiringAssignments->first()->employee->email,
                //     'phone_number' => $hiringRequest->hiringAssignments->first()->employee->phone_number,
                //     'address' => $hiringRequest->hiringAssignments->first()->employee->address,
                //     'date_of_birth' => $hiringRequest->hiringAssignments->first()->employee->date_of_birth,
                //     // Add other fields as needed
                // ]


                : null,
        ], 200);
    }


}
