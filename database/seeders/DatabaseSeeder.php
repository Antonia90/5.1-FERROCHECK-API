<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('local')) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

            $this->call(IngredientSeeder::class); // 🔑 solo en local
        }

        if (app()->environment('testing')) {
            $this->call(\Database\Seeders\PassportTestingSeeder::class);
        }
    }
}
