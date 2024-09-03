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

    // User update

    public function registerStep2(Request $request)
    {





        // Get the authenticated user via JWT
        $user = auth()->user();

        // Check if user exists
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
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
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240'
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

        // Save the user
        $user->step = 2; // Set step value to 2

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'protected'); // Store resume in protected storage
            $user->resume = $resumePath;
        }

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

        return response()->json(['message' => 'User registration completed'], 200);
    }


    // User delete
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // Show user details








}
