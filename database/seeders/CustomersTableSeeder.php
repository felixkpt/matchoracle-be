<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $totalRecords = 55000;
        $batchSize = 100; // Set the desired batch size

        if (Customer::count() >= $totalRecords) return;

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

            $email = $faker->unique()->email;
            $first_name = $faker->firstName;
            $middle_name = $faker->optional()->firstName;
            $last_name = $faker->lastName;
            $name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);

            Customer::updateOrCreate(['email' => $email,], [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'name' => $name,
                'email' => $email,
                'merchant_id' => $faker->randomNumber(),
                'email' => $faker->unique()->safeEmail,
                'customer_type_id' => $faker->randomNumber(),
                'phone' => $faker->optional()->numerify('+254##########'), // Generates a US phone number with country code and 10 digits                ,
                'facebook_thread_id' => $faker->randomNumber(),
                'facebook_id' => $faker->word,
                'alternate_phone' => $faker->optional()->numerify('+254##########'), // Generates a US phone number with country code and 10 digits                ,
                'display_name' => $faker->userName,
                'message_time' => $faker->time(),
                'fb_message_time' => $faker->dateTimeThisYear(),
                'tweet_message_time' => $faker->dateTimeThisYear(),
                'country_id' => $faker->randomNumber(),
                'city_id' => $faker->randomNumber(),
                'business_name' => $faker->company,
                'facebook_page_id' => $faker->randomNumber(),
                'company_id' => $faker->randomNumber(),
                'chat_tag_id' => $faker->randomNumber(1),
                'account_number' => $faker->uuid,
                'line_business_id' => $faker->randomNumber(9),
                'organization_type_id' => $faker->randomNumber(2),
                'order_frequency_id' => $faker->randomNumber(1),
                'order_day_id' => $faker->randomNumber(1),
                'contact_person' => $faker->name,
                'user_id' => User::inRandomOrder()->first()->id,
                'status' => $faker->boolean(90), // 90% chance of being active (status=1)
                'created_at' => Carbon::now()->subMinutes(rand(0, 1440 * 90)), // Subtract a random number of minutes (up to 24 hours * x days)
                'updated_at' => Carbon::now(),

            ]);

            sleep(5);

        }
    }
}
