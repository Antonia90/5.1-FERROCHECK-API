<?php

use App\Models\User;

it('logs out an authenticated user', function () {
    // Arrange: crear usuario y loguearlo para obtener token
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->accessToken;

    // Act: llamar al endpoint con el token
    $response = $this->withHeaders([
        'Authorization' => "Bearer $token",
    ])->postJson('/api/logout');

    // Assert
    $response->assertOk()
             ->assertJson([
                 'message' => 'Successfully logged out',
             ]);
});
