<?php

it('registers a new user', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Username',
        'email' => 'username@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertCreated()
             ->assertJsonStructure(['user', 'token']);
});
