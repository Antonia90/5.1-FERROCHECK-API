<?php

use App\Models\User;
use App\Models\Ingredient;

beforeEach(function () {
    // Creamos un usuario autenticado para cada test
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'api');
});

it('lists all ingredients', function () {
    Ingredient::factory()->count(3)->for($this->user)->create(); // 🔑 Asociado al user autenticado

    $response = $this->getJson('/api/ingredients');

    $response->assertOk()
        ->assertJsonCount(3);
});

it('shows a single ingredient', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create([ // 🔑
        'ingredient_type' => 'verdura',
        'name' => 'Espinaca',
        'iron_mg_per_100g' => 2.7,
    ]);

    $response = $this->getJson("/api/ingredients/{$ingredient->id}");

    $response->assertOk()
        ->assertJson([
            'id' => $ingredient->id,
            'name' => 'Espinaca',
            'ingredient_type' => 'verdura',
            'iron_mg_per_100g' => 2.7,
        ]);
});

it('creates a new ingredient', function () {
    $payload = [
        'ingredient_type' => 'fruta',
        'name' => 'Manzana',
        'iron_mg_per_100g' => 0.1,
    ];

    $response = $this->postJson('/api/ingredients', $payload);

    $response->assertCreated()
        ->assertJsonFragment($payload);

    $this->assertDatabaseHas('ingredients', array_merge($payload, [
        'user_id' => $this->user->id, // 🔑 Verificación con user_id correcto
    ]));
});

it('updates an ingredient', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create([ // 🔑
        'ingredient_type' => 'proteina',
        'name' => 'Lentejas',
        'iron_mg_per_100g' => 3.3,
    ]);

    $payload = [
        'ingredient_type' => 'proteina',
        'name' => 'Lentejas Rojas',
        'iron_mg_per_100g' => 3.5,
    ];

    $response = $this->putJson("/api/ingredients/{$ingredient->id}", $payload);

    $response->assertOk()
        ->assertJsonFragment($payload);

    $this->assertDatabaseHas('ingredients', array_merge($payload, [
        'id' => $ingredient->id,
        'user_id' => $this->user->id, // 🔑
    ]));
});

it('deletes an ingredient', function () {
    $ingredient = Ingredient::factory()->for($this->user)->create(); // 🔑

    $response = $this->deleteJson("/api/ingredients/{$ingredient->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('ingredients', [
        'id' => $ingredient->id,
    ]);
});

describe('permissions', function () {
    it('prevents unauthenticated users from accessing ingredients', function () {
        $this->postJson('/logout'); // forzamos logout si hay
        auth()->logout();

        $response = $this->getJson('/api/ingredients');
        $response->assertUnauthorized(); // 401
    });

    it('allows admin to manage all ingredients', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'api');

        $ingredient = Ingredient::factory()->create(); // de cualquier usuario

        // Admin puede actualizar
        $payload = ['ingredient_type' => 'verdura', 'name' => 'Acelga', 'iron_mg_per_100g' => 2.0];
        $this->putJson("/api/ingredients/{$ingredient->id}", $payload)
             ->assertOk()
             ->assertJsonFragment($payload);

        // Admin puede borrar
        $this->deleteJson("/api/ingredients/{$ingredient->id}")
             ->assertNoContent();
    });

    it('allows user to manage only their own ingredients', function () {
        $otherUser = User::factory()->create();
        $ingredient = Ingredient::factory()->for($otherUser)->create();

        // Usuario normal no puede modificar ni borrar ajenos
        $payload = ['ingredient_type' => 'fruta', 'name' => 'Pera', 'iron_mg_per_100g' => 0.2];
        $this->putJson("/api/ingredients/{$ingredient->id}", $payload)
             ->assertForbidden();

        $this->deleteJson("/api/ingredients/{$ingredient->id}")
             ->assertForbidden();
    });
});

