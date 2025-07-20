<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Number of customers to seed
        $batchSize = 500;
        $totalCustomers = 150000;

        DB::transaction(function () use ($batchSize, $totalCustomers) {
            for ($i = 0; $i < $totalCustomers; $i += $batchSize) {
                // Create customers in batches
                $customers = Customer::factory()->count($batchSize)->make()->toArray();

                // Disable model events and insert customers
                Customer::withoutEvents(function () use ($customers) {
                    Customer::insert($customers);
                });
            }
        });
    }
}
