<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            User::factory()->raw(['name' => 'Test User', 'email' => 'test@example.com'])
        );

        Website::factory()
            ->for($user)
            ->has(
                Page::factory()
                    ->count(3)
                    ->has(
                        Scan::factory()
                            ->has(ScanResult::factory())
                    )
            )
            ->count(3)
            ->create();
    }
}
