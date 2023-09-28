<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\Client;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Set the total number you want to seed
        $totalRecords = 11100;
        $batchSize = 100; // Set the desired batch size

        if (User::count() >= $totalRecords) return;

        $totalBatches = ceil($totalRecords / $batchSize);

        for ($batch = 1; $batch <= $totalBatches; $batch++) {
            $start = ($batch - 1) * $batchSize + 1;
            $end = min($batch * $batchSize, $totalRecords);

            $this->seedBatch($start, $end);

            // "Behold, the 'Sleepy Seeder' granting the server some shut-eye between batches, dreaming of data wonders! 😴💤"
            sleep(60);
        }
    }

    /**
     * Seed a batch of records.
     *
     * @param int $start
     * @param int $end
     * @return void
     */
    private function seedBatch($start, $end)
    {

        $faker = Faker::create();

        for ($i = $start; $i <= $end; $i++) {

            // Your API endpoint URL
            $apiUrl = 'https://randomuser.me/api';

            $userFromApi = null;
            try {
                // Fetch data from the API
                $response = file_get_contents($apiUrl);

                // Convert JSON response to a PHP object
                $data = json_decode($response);

                // Get the user data from the API response
                $userFromApi = $data->results[0];
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }

            // Fill in the user data with fake data where necessary
            $first_name = $userFromApi->name->first ?? $faker->firstName;
            $middle_name = $userFromApi->name->middle ?? $faker->optional()->firstName;
            $last_name = $userFromApi->name->last ?? $faker->lastName;
            $name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);

            $email = $userFromApi->email ?? $faker->unique()->email;
            $phone = $faker->optional()->numerify('+254##########');

            // Generate other fields with fake data
            $two_factor_valid = $faker->boolean;
            $last_login_date = $faker->optional()->dateTimeThisYear;
            $two_factor_expires_at = $faker->optional()->dateTimeThisYear;
            $two_factor_code = null;
            $email_verified_at = $faker->optional()->dateTimeThisYear;
            $password = Hash::make($email); // You can replace 'password' with the desired password
            $api_token = $faker->optional()->sha1;
            $session_id = null;
            $is_session_valid = 0;
            $allowed_session_no = 1;
            $is_online = 0;
            $remember_token = null;
            $two_factor_enabled = $faker->boolean(85);
            $is_calltronix = $faker->boolean(70);
            $theme = Arr::random(['light', 'dark']);
            $user_id = User::inRandomOrder()->first()->id ?? 0;
            $status = $faker->boolean(85);

            $user = User::updateOrCreate(['email' => $email,], [
                'first_name' => $first_name,
                'middle_name' => $middle_name,
                'last_name' => $last_name,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'two_factor_valid' => $two_factor_valid,
                'last_login_date' => $last_login_date,
                'two_factor_expires_at' => $two_factor_expires_at,
                'two_factor_code' => $two_factor_code,
                'email_verified_at' => $email_verified_at,
                'password' => $password,
                'api_token' => $api_token,
                'session_id' => $session_id,
                'is_session_valid' => $is_session_valid,
                'allowed_session_no' => $allowed_session_no,
                'is_online' => $is_online,
                'remember_token' => $remember_token,
                'two_factor_enabled' => $two_factor_enabled,
                'is_calltronix' => $is_calltronix,
                'theme' => $theme,
                'user_id' => $user_id,
                'status' => $status,
                'created_at' => Carbon::now()->subMinutes(rand(0, 1440 * 90)), // Subtract a random number of minutes (up to 24 hours * x days)
                'updated_at' => Carbon::now(),
            ]);

            if (isset($userFromApi->picture->large) && $user->wasRecentlyCreated) {

                $this->saveAvatar($userFromApi, $user);

                // Generate a random number between 1 and 5
                $numRoles = rand(1, 5);

                // Get $numRoles random role ids
                $roleIds = Role::inRandomOrder()->limit($numRoles)->pluck('id')->toArray();

                try {
                    // Assign roles to the user
                    $user->syncRoles($roleIds);
                } catch (Exception $e) {
                    Log::critical('Roles/role notfound::', ['message' => $e->getMessage()]);
                }
            }

            sleep(5);

        }
    }

    function saveAvatar($userFromApi, $user)
    {

        // Get the current date using Carbon
        $now = Carbon::now();

        // Define the directory where the attachments will be saved
        $attachmentFolder = 'users/' . $now->year . '/' . $now->month . '/' . $now->day;

        $attachmentFilename = 'user_' . $user->id . '_' . now()->format('Ymd_His') . '.jpg'; // Use the desired file extension

        // Download the avatar image and save it to the user's avatar field
        // Randomly choose to use the default avatar URL (70% probability) or download the image (30% probability)
        $avatar = rand(1, 10) <= 3 ? $userFromApi->picture->large : Client::downloadFileFromUrl($userFromApi->picture->large, $attachmentFolder, $attachmentFilename);

        $user->avatar = $avatar;
        $user->save();
    }
}
