<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('allows a person to log in', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertOk()
             ->assertJsonStructure(['token']);
});