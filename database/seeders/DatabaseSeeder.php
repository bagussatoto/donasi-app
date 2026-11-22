<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Urutan penting: CampaignSeeder bergantung pada data CategorySeeder
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            CampaignSeeder::class,
            AccountSeeder::class,
        ]);
    }
}
