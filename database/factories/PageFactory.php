<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
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
            'url' => $this->faker->url(),
            'page_type' => $this->faker->randomElement(['homepage', 'cms', 'category', 'product', 'custom']),
            'enabled' => true,
        ];
    }
}
