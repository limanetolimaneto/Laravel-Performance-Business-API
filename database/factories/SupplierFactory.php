<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nationalities =['ZA','US','EN'];
        return [
            'name' => fake()->name(),
            'address' => fake()->streetAddress(),
            'nationality' => $nationalities[rand(0,2)],
            'phone_number' => fake()->phoneNumber(),
        ];
    }
}
