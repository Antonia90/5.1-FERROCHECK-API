<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Laravel\Passport\Client;

class PassportTestingSeeder extends Seeder
{
    public function run(): void
    {
        // Si ya existe, no duplicar
        $exists = Client::query()
            ->where('provider', 'users')
            ->where('personal_access_client', true)
            ->where('revoked', false)
            ->exists();

        if ($exists) {
            return;
        }

        // Asegura que se puedan asignar masivamente
        Client::unguard();

        Client::forceCreate([
            'id' => (string) Str::uuid(),                   // PK UUID
            'name' => config('app.name').' Personal Access Client',
            'secret' => Str::random(40),                    // cualquier string
            'provider' => 'users',                          // MUY IMPORTANTE: coincide con config/auth.php
            'redirect_uris' => ['http://localhost'],
            'grant_types' => ['personal_access'],
            'personal_access_client' => true,
            'revoked' => false,
        ]);
    }
}
