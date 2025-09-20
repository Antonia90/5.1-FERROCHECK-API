<?php

use App\Models\User;
use App\Models\Ingredient;

beforeEach(function () {
    // Creamos un usuario autenticado para cada test
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'api');
});

it('lists all ingredients', function () {
    Ingredient::factory()->count(3)->create();

    $response = $this->getJson('/api/ingredients');

    $response->assertOk()
        ->assertJsonCount(3);
});

it('shows a single ingredient', function () {
    $ingredient = Ingredient::factory()->create([
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

    $this->assertDatabaseHas('ingredients', $payload);
});

it('updates an ingredient', function () {
    $ingredient = Ingredient::factory()->create([
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

    $this->assertDatabaseHas('ingredients', $payload);
});

it('deletes an ingredient', function () {
    $ingredient = Ingredient::factory()->create();

    $response = $this->deleteJson("/api/ingredients/{$ingredient->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('ingredients', [
        'id' => $ingredient->id,
    ]);
});
