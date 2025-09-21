<?php

use App\Models\User;
use App\Models\Ingredient;

// Helpers para crear usuarios con rol
function createUserWithRole(string $role): User {
    $user = User::factory()->create();
    $user->assignRole($role);
    return $user;
}

beforeEach(function () {
    $this->admin = createUserWithRole('admin');
    $this->user  = createUserWithRole('user');
});

// --- Invitado (sin login) ---
it('denies guests from accessing ingredients', function () {
    $ingredient = Ingredient::factory()->create();

    $this->getJson('/api/ingredients')->assertUnauthorized();
    $this->getJson("/api/ingredients/{$ingredient->id}")->assertUnauthorized();
    $this->postJson('/api/ingredients', [])->assertUnauthorized();
    $this->putJson("/api/ingredients/{$ingredient->id}", [])->assertUnauthorized();
    $this->deleteJson("/api/ingredients/{$ingredient->id}")->assertUnauthorized();
});

// --- Admin ---
it('allows admin to manage any ingredient', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->admin, 'api');

    $this->getJson('/api/ingredients')->assertOk();
    $this->getJson("/api/ingredients/{$ingredient->id}")->assertOk();

    $payload = [
        'ingredient_type' => 'fruta',
        'name' => 'Banana',
        'iron_mg_per_100g' => 0.3,
    ];

    $this->postJson('/api/ingredients', $payload)->assertCreated();
    $this->putJson("/api/ingredients/{$ingredient->id}", $payload)->assertOk();
    $this->deleteJson("/api/ingredients/{$ingredient->id}")->assertNoContent();
});

// --- Usuario común ---
it('allows user to create and view ingredients', function () {
    $this->actingAs($this->user, 'api');

    $payload = [
        'ingredient_type' => 'verdura',
        'name' => 'Zanahoria',
        'iron_mg_per_100g' => 0.6,
    ];

    $this->postJson('/api/ingredients', $payload)->assertCreated();
    $this->getJson('/api/ingredients')->assertOk();
});

it('prevents user from updating or deleting others ingredients', function () {
    $otherUser = createUserWithRole('user');
    $ingredient = Ingredient::factory()->for($otherUser)->create();

    $this->actingAs($this->user, 'api');

    $payload = [
        'ingredient_type' => 'proteina',
        'name' => 'Pollo',
        'iron_mg_per_100g' => 1.5,
    ];

    $this->putJson("/api/ingredients/{$ingredient->id}", $payload)->assertForbidden();
    $this->deleteJson("/api/ingredients/{$ingredient->id}")->assertForbidden();
});

it('allows user to update and delete their own ingredients', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create();

    $this->actingAs($this->user, 'api');

    $payload = [
        'ingredient_type' => 'proteina',
        'name' => 'Lentejas',
        'iron_mg_per_100g' => 3.3,
    ];

    $this->putJson("/api/ingredients/{$ingredient->id}", $payload)->assertOk();
    $this->deleteJson("/api/ingredients/{$ingredient->id}")->assertNoContent();
});
