<?php

use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;

it('lists all recipes for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    Recipe::factory()->count(3)->for($user)->create();

    $response = $this->getJson('/api/recipes');

    $response->assertOk()
        ->assertJsonCount(3);
});

it('shows a single recipe', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $recipe = Recipe::factory()->for($user)->create([
        'name' => 'Ensalada de lentejas',
        'description' => 'Receta fresca y rica en hierro',
        'diet_category' => 'vegetariana',
        'base_servings' => 2,
    ]);

    $response = $this->getJson("/api/recipes/{$recipe->id}");

    $response->assertOk()
        ->assertJson([
            'id' => $recipe->id,
            'name' => 'Ensalada de lentejas',
            'diet_category' => 'vegetariana',
            'base_servings' => 2,
        ]);
});

it('creates a new recipe', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $payload = [
        'name' => 'Guiso de garbanzos',
        'description' => 'Un guiso vegano con alto contenido en hierro',
        'diet_category' => 'vegana',
        'base_servings' => 4,
    ];

    $response = $this->postJson('/api/recipes', $payload);

    $response->assertCreated()
        ->assertJsonFragment($payload);

    $this->assertDatabaseHas('recipes', array_merge($payload, [
        'user_id' => $user->id,
    ]));
});

it('updates a recipe owned by the user', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $recipe = Recipe::factory()->for($user)->create();

    $payload = [
        'name' => 'Guiso de lentejas',
        'description' => 'Clásico guiso vegetariano',
        'diet_category' => 'vegetariana',
        'base_servings' => 3,
    ];

    $response = $this->putJson("/api/recipes/{$recipe->id}", $payload);

    $response->assertOk()
        ->assertJsonFragment($payload);

    $this->assertDatabaseHas('recipes', array_merge($payload, [
        'id' => $recipe->id,
        'user_id' => $user->id,
    ]));
});

it('deletes a recipe owned by the user', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $recipe = Recipe::factory()->for($user)->create();

    $response = $this->deleteJson("/api/recipes/{$recipe->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('recipes', [
        'id' => $recipe->id,
    ]);
});

describe('permissions', function () {
    it('prevents unauthenticated users from accessing recipes', function () {
        $response = $this->getJson('/api/recipes');
        $response->assertUnauthorized();
    });

    it('allows admin to manage all recipes', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'api');

        $recipe = Recipe::factory()->create();

        $payload = [
            'name' => 'Paella de verduras',
            'description' => 'Versión vegana con garbanzos',
            'diet_category' => 'vegana',
            'base_servings' => 5,
        ];

        // Admin puede actualizar
        $this->putJson("/api/recipes/{$recipe->id}", $payload)
            ->assertOk()
            ->assertJsonFragment($payload);

        // Admin puede borrar
        $this->deleteJson("/api/recipes/{$recipe->id}")
            ->assertNoContent();
    });

    it('prevents users from updating/deleting recipes from others', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $otherUser = User::factory()->create();
        $recipe = Recipe::factory()->for($otherUser)->create();

        $payload = [
            'name' => 'Sopa de miso',
            'description' => 'Clásica japonesa',
            'diet_category' => 'vegana',
            'base_servings' => 2,
        ];

        $this->putJson("/api/recipes/{$recipe->id}", $payload)
            ->assertForbidden();

        $this->deleteJson("/api/recipes/{$recipe->id}")
            ->assertForbidden();
    });

    it('allows user to create and view their own recipes', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $payload = [
            'name' => 'Pizza vegetariana',
            'description' => 'Con masa integral y queso',
            'diet_category' => 'vegetariana',
            'base_servings' => 2,
        ];

        // Crear
        $response = $this->postJson('/api/recipes', $payload);
        $response->assertCreated()
            ->assertJsonFragment($payload);

        // Ver en listado
        $this->getJson('/api/recipes')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Pizza vegetariana']);
    });
});

describe('filters', function () {
    it('filters recipes by diet_category', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        Recipe::factory()->for($user)->create(['diet_category' => 'vegana']);
        Recipe::factory()->for($user)->create(['diet_category' => 'vegetariana']);

        $response = $this->getJson('/api/recipes?diet_category=vegana');

        $response->assertOk()
            ->assertJsonFragment(['diet_category' => 'vegana'])
            ->assertJsonMissing(['diet_category' => 'vegetariana']);
    });
});