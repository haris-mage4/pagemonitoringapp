<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Scan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scan>
 */
class ScanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'status' => 'completed',
            'trigger' => $this->faker->randomElement(['schedule', 'webhook', 'manual']),
            'started_at' => now(),
            'finished_at' => now(),
        ];
    }
}
