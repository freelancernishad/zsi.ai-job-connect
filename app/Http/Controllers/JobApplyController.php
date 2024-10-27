<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobApplicationMail; // Create this Mailable
use Illuminate\Support\Facades\Auth;

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
            'username' => $user->name, // Get authenticated user's name
            'name' => $user->name, // Auth user name
            'email' => $user->email,    // Auth user email
            'phone' => $user->phone_number,     // Auth user phone
            'address' => $user->address,  // Auth user address
        ];

        // Send the email
        Mail::to('freelancernishad123@gmail.com') // Change to the recipient's email
            ->send(new JobApplicationMail($data));

        return response()->json(['message' => 'Application sent successfully!']);
    }
}
