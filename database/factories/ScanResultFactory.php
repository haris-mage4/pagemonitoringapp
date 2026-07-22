<?php

namespace Database\Factories;

use App\Models\Scan;
use App\Models\ScanResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScanResult>
 */
class ScanResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scan_id' => Scan::factory(),
            'device' => 'mobile',
            'performance' => $this->faker->numberBetween(0, 100),
            'accessibility' => $this->faker->numberBetween(0, 100),
            'seo' => $this->faker->numberBetween(0, 100),
            'best_practices' => $this->faker->numberBetween(0, 100),
            'fcp' => $this->faker->numberBetween(500, 4000),
            'lcp' => $this->faker->numberBetween(1000, 6000),
            'cls' => $this->faker->randomFloat(3, 0, 0.5),
            'tbt' => $this->faker->numberBetween(0, 1000),
            'speed_index' => $this->faker->numberBetween(1000, 8000),
            'tti' => $this->faker->numberBetween(1000, 8000),
            'raw_json' => ['lighthouseVersion' => '12.0.0'],
            'exit_code' => 0,
            'error_message' => null,
        ];
    }
}
