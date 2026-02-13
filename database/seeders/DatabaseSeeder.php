<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in correct order (respecting foreign keys)
        $this->call([
            ProductStatusSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
