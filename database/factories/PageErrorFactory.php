<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageError;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageError>
 */
class PageErrorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $message = $this->faker->sentence();

        return [
            'page_id' => Page::factory(),
            'fingerprint' => hash('sha256', $message),
            'message' => $message,
            'source' => $this->faker->url().':1',
            'stack' => null,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'occurrence_count' => 1,
        ];
    }
}
