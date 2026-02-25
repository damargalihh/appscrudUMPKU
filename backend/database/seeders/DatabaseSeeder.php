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
        // User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Full Admin',
            'email' => 'admin@umpku.ac.id',
            'password' => bcrypt('admin123'),
            'is_admin' => true,
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
    }
}
