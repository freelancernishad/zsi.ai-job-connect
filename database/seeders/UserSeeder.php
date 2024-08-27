<?php
// php artisan db:seed --class=UserSeeder

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Language;
use App\Models\Certification;
use App\Models\Skill;
use App\Models\Education;
use App\Models\EmploymentHistory;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {
            $user = User::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'date_of_birth' => $faker->date(),
                'profile_picture' => $faker->imageUrl(),
                'preferred_job_title' => $faker->jobTitle,
                'description' => $faker->paragraph,
                'years_of_experience_in_the_industry' => $faker->numberBetween(1, 30),
                'preferred_work_state' => $faker->stateAbbr,
                'preferred_work_zipcode' => $faker->postcode,
                'your_experience' => $faker->paragraph,
                'familiar_with_safety_protocols' => $faker->boolean,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'), // default password
            ]);

            // Seeding related data

            // Languages
            for ($j = 0; $j < rand(1, 3); $j++) {
                Language::create([
                    'user_id' => $user->id,
                    'language' => $faker->languageCode,
                    'level' => $faker->randomElement(['Basic', 'Intermediate', 'Advanced', 'Native']),
                ]);
            }

            // Certifications
            for ($j = 0; $j < rand(1, 2); $j++) {
                Certification::create([
                    'user_id' => $user->id,
                    'name' => $faker->word,
                    'certified_from' => $faker->company,
                    'year' => $faker->year,
                ]);
            }

            // Skills
            for ($j = 0; $j < rand(2, 5); $j++) {
                Skill::create([
                    'user_id' => $user->id,
                    'name' => $faker->word,
                    'level' => $faker->randomElement(['Beginner', 'Intermediate', 'Expert']),
                ]);
            }

            // Education
            for ($j = 0; $j < rand(1, 3); $j++) {
                Education::create([
                    'user_id' => $user->id,
                    'school_name' => $faker->company,
                    'qualifications' => $faker->word,
                    'start_date' => $faker->date(),
                    'end_date' => $faker->date(),
                    'notes' => $faker->sentence,
                ]);
            }

            // Employment History
            for ($j = 0; $j < rand(1, 3); $j++) {
                $start_date = $faker->date();
                $end_date = $faker->dateTimeBetween($start_date, 'now')->format('Y-m-d');

                Education::create([
                    'user_id' => $user->id,
                    'school_name' => $faker->company,
                    'qualifications' => $faker->word,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'notes' => $faker->sentence,
                ]);
            }
        }
    }
}
