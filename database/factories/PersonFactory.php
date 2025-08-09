<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\StatusEnum;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'serial_passport' => fake()->unique()->randomNumber(4,true),
            'number_passport' => fake()->unique()->randomNumber(6,true),
            'status' => (mt_rand(0,1) === 0 ? StatusEnum::PROHIBITED : StatusEnum::ALLOWED)
        ];
    }
}
