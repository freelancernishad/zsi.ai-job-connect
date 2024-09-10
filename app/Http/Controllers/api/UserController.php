<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{



    public function registerStep2(Request $request)
    {
        // Get the authenticated user via JWT
        $user = auth()->user();

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'The user you are trying to access does not exist. Please check the user ID and try again.',
            ], 404);
        }

        // Check if the user's email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Your email address is not verified. Please verify your email before proceeding.',
            ], 403);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            // Existing validation rules
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone_number' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('users')->ignore($user->id),
            ],
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable',
            'profile_picture' => 'nullable|string|max:255',
            'preferred_job_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'years_of_experience_in_the_industry' => 'nullable|string',
            'preferred_work_state' => 'nullable|string|max:255',
            'preferred_work_zipcode' => 'nullable|string|max:10',
            'your_experience' => 'nullable|string',
            'familiar_with_safety_protocols' => 'nullable|boolean',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',

            // Validation for related models
            'languages' => 'nullable|array',
            'languages.*.language' => 'required_with:languages|string|max:255',
            'languages.*.level' => 'required_with:languages|string|max:255',

            'certifications' => 'nullable|array',
            'certifications.*.name' => 'required_with:certifications|string|max:255',
            'certifications.*.certified_from' => 'required_with:certifications|string|max:255',
            'certifications.*.year' => 'required_with:certifications|integer|digits:4',

            'skills' => 'nullable|array',
            'skills.*.name' => 'required_with:skills|string|max:255',
            'skills.*.level' => 'required_with:skills|string|max:255',

            'education' => 'nullable|array',
            'education.*.school_name' => 'required_with:education|string|max:255',
            'education.*.qualifications' => 'required_with:education|string|max:255',
            'education.*.start_date' => 'required_with:education',
            'education.*.end_date' => 'nullable|after_or_equal:education.*.start_date',
            'education.*.notes' => 'nullable|string',

            'employment_history' => 'nullable|array',
            'employment_history.*.company' => 'required_with:employment_history|string|max:255',
            'employment_history.*.position' => 'required_with:employment_history|string|max:255',
            'employment_history.*.start_date' => 'required_with:employment_history',
            'employment_history.*.end_date' => 'nullable|after_or_equal:employment_history.*.start_date',
            'employment_history.*.responsibilities' => 'nullable|string',

            // Validation for UserLookingService
            'looking_services' => 'nullable|array',
            'looking_services.*' => 'required|exists:services,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Update the user's fields
        $user->first_name = $request->first_name ?? $user->first_name;
        $user->last_name = $request->last_name ?? $user->last_name;
        $user->phone_number = $request->phone_number ?? $user->phone_number;
        $user->address = $request->address ?? $user->address;
        $user->date_of_birth = date("Y-m-d", strtotime($request->date_of_birth)) ?? date("Y-m-d", strtotime($user->date_of_birth));
        $user->profile_picture = $request->profile_picture ?? $user->profile_picture;
        $user->preferred_job_title = $request->preferred_job_title ?? $user->preferred_job_title;
        $user->description = $request->description ?? $user->description;
        $user->years_of_experience_in_the_industry = $request->years_of_experience_in_the_industry ?? $user->years_of_experience_in_the_industry;
        $user->preferred_work_state = $request->preferred_work_state ?? $user->preferred_work_state;
        $user->preferred_work_zipcode = $request->preferred_work_zipcode ?? $user->preferred_work_zipcode;
        $user->your_experience = $request->your_experience ?? $user->your_experience;
        $user->familiar_with_safety_protocols = $request->familiar_with_safety_protocols ?? $user->familiar_with_safety_protocols;

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'protected'); // Store resume in protected storage
            $user->resume = $resumePath;
        }

        // Update step
        $user->step = 2; // Set step value to 2

        // Update employer_step and status if the role is EMPLOYER
        if ($user->role === 'EMPLOYER') {
            $user->employer_step = 2; // Or whatever value is appropriate
            $user->status = 'active'; // Set status to active
        }

        // Save the user
        $user->save();

        // Update related models: languages, certifications, skills, education, etc.
        if ($request->has('languages')) {
            foreach ($request->languages as $languageData) {
                $language = $user->languages()->updateOrCreate(
                    ['id' => $languageData['id'] ?? null],
                    [
                        'language' => $languageData['language'],
                        'level' => $languageData['level'],
                    ]
                );
            }
        }

        if ($request->has('certifications')) {
            foreach ($request->certifications as $certificationData) {
                $certification = $user->certifications()->updateOrCreate(
                    ['id' => $certificationData['id'] ?? null],
                    [
                        'name' => $certificationData['name'],
                        'certified_from' => $certificationData['certified_from'],
                        'year' => $certificationData['year'],
                    ]
                );
            }
        }

        if ($request->has('skills')) {
            foreach ($request->skills as $skillData) {
                $skill = $user->skills()->updateOrCreate(
                    ['id' => $skillData['id'] ?? null],
                    [
                        'name' => $skillData['name'],
                        'level' => $skillData['level'],
                    ]
                );
            }
        }

        if ($request->has('education')) {
            foreach ($request->education as $educationData) {
                $education = $user->education()->updateOrCreate(
                    ['id' => $educationData['id'] ?? null],
                    [
                        'school_name' => $educationData['school_name'],
                        'qualifications' => $educationData['qualifications'],
                        'start_date' => date("Y-m-d", strtotime($educationData['start_date'])),
                        'end_date' => date("Y-m-d", strtotime($educationData['end_date'])),
                        'notes' => $educationData['notes'],
                    ]
                );
            }
        }

        if ($request->has('employment_history')) {
            foreach ($request->employment_history as $employmentData) {
                $employment = $user->employmentHistory()->updateOrCreate(
                    ['id' => $employmentData['id'] ?? null],
                    [
                        'company' => $employmentData['company'],
                        'position' => $employmentData['position'],
                        'start_date' => date("Y-m-d", strtotime($employmentData['start_date'])),
                        'end_date' => date("Y-m-d", strtotime($employmentData['end_date'])),
                        'responsibilities' => $employmentData['responsibilities'],
                    ]
                );
            }
        }

        // Update looking services
        if ($request->has('looking_services')) {
            $serviceIds = $request->looking_services;

            // Delete existing looking services for this user
            $user->lookingServices()->delete();

            // Create new looking services
            foreach ($serviceIds as $serviceId) {
                $user->lookingServices()->create([
                    'service_id' => $serviceId,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully. You can now proceed to the next step.',
        ], 200);
    }




    public function registerStep3()
    {
        $user = Auth::user();

        // Check if user status is inactive
        if ($user->status === 'inactive') {
            // Check user step
            if ($user->step === 2) {
                // Check if payment has already been made
                if ($user->activation_payment_made) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Activation payment has already been processed for this user. Please contact the admin for further assistance.',
                    ], 400);
                }

                // Call the createPayment method and pass the amount
                $paymentResponse = createPayment(100);

                // Ensure paymentResponse is an array
                if (is_array($paymentResponse) && $paymentResponse['success']) {
                    // Update user to indicate that payment has been made
                    $user->update(['activation_payment_made' => true]);
                    return response()->json($paymentResponse);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'There was an issue creating the payment. Please try again later or contact support if the problem persists.',
                    ], 500);
                }
            } elseif ($user->step === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete Step 2 before proceeding to the payment process.',
                ], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'The user is in an unexpected state. Please contact support for assistance.',
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'The user is already active and does not need to complete this step again.',
            ], 400);
        }
    }




    // User delete
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'The requested user could not be found. Please verify the user ID and try again.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'The user has been successfully deleted.',
        ], 200);
    }

    // Show user details
    public function getUserByUsername(string $username)
    {
        // Find the user by username and load all related data
        $user = User::where('username', $username)
                    ->with([
                        'languages',         // Load user's languages
                        'certifications',    // Load user's certifications
                        'skills',            // Load user's skills
                        'education',         // Load user's education
                        'employmentHistory', // Load user's employment history
                        'resumes',           // Load user's resumes
                        'hiringSelections',  // Load user's hiring selections
                        'hiringAssignments', // Load user's hiring assignments
                        'assignedHiringAssignments', // Load hiring assignments assigned to the user
                        'servicesLookingFor' // Load hiring assignments assigned to the user
                    ])
                    ->first();

        // Check if the user was found
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Return the user data along with related data as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully.',
            'data' => $user,
        ], 200);
    }






}
