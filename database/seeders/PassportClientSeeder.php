<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PassportClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exists = DB::table('oauth_clients')
            ->where('personal_access_client', 1)
            ->where('provider', 'users')
            ->where('revoked', 0)
            ->exists();

        if (!$exists) {
            DB::table('oauth_clients')->insert([
                'id' => (string) Str::uuid(),
                'owner_type' => null,
                'owner_id' => null,
                'name' => config('app.name') . ' Personal Access Client',
                'secret' => Str::random(40),
                'provider' => 'users',
                'redirect_uris' => json_encode(['http://localhost']),
                'grant_types' => json_encode(['personal_access']),
                'personal_access_client' => 1,
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
