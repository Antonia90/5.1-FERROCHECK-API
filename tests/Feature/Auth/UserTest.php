<?php

use App\Models\User;

it('returns authenticated user profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->accessToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer $token",
    ])->getJson('/api/user');

    $response->assertOk()
             ->assertJson([
                 'id' => $user->id,
                 'email' => $user->email,
                 'name' => $user->name,
             ]);
});

it('rejects unauthenticated request to profile', function () {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});
