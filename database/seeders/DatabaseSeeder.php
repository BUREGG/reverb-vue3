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

        User::factory(6)->create();
        User::factory()->create([
            'name' => 'test',
            'email' => 'test@test.pl',
            'password' => '12345678'
        ]);
        User::factory()->create([
            'name' => 'test2',
            'email' => 'test2@test.pl',
            'password' => '12345678'
        ]);

    }
}
