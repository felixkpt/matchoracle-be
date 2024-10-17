<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone_number' => $this->faker->phoneNumber,
            'alternate_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            // 'email' => $this->faker->safeEmail . uniqid(),
            'status' => $this->faker->boolean(90), // 90% chance of being true
        ];
    }
}
