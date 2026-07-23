<?php

namespace Database\Factories;

use App\Models\Website;
use App\Models\UptimeCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UptimeCheck>
 */
class UptimeCheckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'status' => 'online',
            'http_code' => 200,
            'response_time_ms' => $this->faker->numberBetween(50, 800),
            'checked_at' => now(),
        ];
    }
}
