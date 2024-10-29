<?php

namespace App\Http\Controllers;

use App\Models\JobApply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobApplicationMail; // Create this Mailable

class JobApplyController extends Controller
{
    /**
     * Handle the job application and send an email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


     public function sendMail(Request $request)
     {
         // Validate the request data
         $request->validate([
             'title' => 'required|string|max:255',
             'service' => 'required|string|max:255',
             'location' => 'required|string|max:255',
             'employment_type' => 'required|array',
             'employment_type.*' => 'string|max:255',
             'hourly_rate_min' => 'required|numeric|min:0',
             'hourly_rate_max' => 'required|numeric|min:0',
             'note' => 'nullable|string',
         ]);
     
         $user = Auth::user();
     
         // Prepare email data
         $data = [
             'title' => $request->input('title'),
             'service' => $request->input('service'),
             'location' => $request->input('location'),
             'employment_type' => $request->input('employment_type'),
             'hourly_rate_min' => $request->input('hourly_rate_min'),
             'hourly_rate_max' => $request->input('hourly_rate_max'),
             'note' => $request->input('note'),
             'username' => $user->name,
             'name' => $user->name,
             'email' => $user->email,
             'phone' => $user->phone_number,
             'address' => $user->address,
         ];
     
         // Check if the user role is EMPLOYEE
         if ($user->role === 'EMPLOYEE') {
             // Save the job application to the database
             $jobApply = JobApply::create([
                 'user_id' => $user->id,
                 'title' => $data['title'],
                 'service' => $data['service'],
                 'location' => $data['location'],
                 'employment_type' => json_encode($data['employment_type']), // Convert to JSON
                 'hourly_rate_min' => $data['hourly_rate_min'],
                 'hourly_rate_max' => $data['hourly_rate_max'],
                 'note' => $data['note'],
             ]);
     
             // Optionally send the email
             // Mail::to('freelancernishad123@gmail.com') // Change to the recipient's email
             //     ->send(new JobApplicationMail($data));
     
             return response()->json(['message' => 'Application sent successfully!']);
         }
     
         return response()->json(['message' => 'You are not authorized to apply for jobs.'], 403);
     }
     


    public function getJobApplies(Request $request)
    {
        // Get search query and per_page parameter from the request
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10); // Default to 10 if not provided

        // Build the query for job applications
        $query = JobApply::with('user'); // Eager load the associated user

        // If there's a search query, apply the filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('service', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone_number', 'like', '%' . $search . '%');
                  });
            });
        }

        // Retrieve the latest job applications, ordered by the created_at field in descending order
        $jobApplies = $query->orderBy('created_at', 'desc')->paginate($perPage); // Use per_page value

        // Check if job applications exist
        if ($jobApplies->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No job applications found.'], 404);
        }

        // Return the list of job applications with users
        return response()->json(['success' => true, 'message' => 'Job applications retrieved successfully.', 'data' => $jobApplies]);
    }


}
