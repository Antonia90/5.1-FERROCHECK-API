<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Roles y permisos básicos ---
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);

        // --- Entorno local ---
        if (app()->environment('local')) {
            // Admin user
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
            $admin->assignRole('admin');

            // Regular user
            $user = User::factory()->create([
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
            ]);
            $user->assignRole('user');

            // Otros seeders
            $this->call([
                IngredientSeeder::class,
                PassportClientSeeder::class,
            ]);
        }

        // --- Entorno testing ---
        if (app()->environment('testing')) {
            $this->call(\Database\Seeders\PassportTestingSeeder::class);
        }
    }
}
