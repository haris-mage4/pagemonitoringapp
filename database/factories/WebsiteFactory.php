<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Website>
 */
class WebsiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company(),
            'base_url' => $this->faker->url(),
            'environment' => $this->faker->randomElement(['production', 'staging']),
            'schedule' => $this->faker->randomElement(['hourly', 'every_6_hours', 'daily', 'weekly']),
            'enabled' => true,
        ];
    }
}
