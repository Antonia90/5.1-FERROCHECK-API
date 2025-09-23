<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('rejects duplicate emails on register', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Other',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

it('rejects registration if password confirmation does not match', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'User',
        'email' => 'user@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['password']);
});
