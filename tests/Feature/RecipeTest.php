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
    $ingredient = Ingredient::factory()->create();
    $payload = [
        'name' => 'Guiso de garbanzos',
        'description' => 'Un guiso vegano con alto contenido en hierro',
        'diet_category' => 'vegana',
        'base_servings' => 4,
        'ingredients' => [
            [
                'id' => $ingredient->id,
                'unit' => 'g',
                'quantity_per_serving' => 200,
            ],
        ],
    ];

    $response = $this->postJson('/api/recipes', $payload);

    $response->assertCreated()
        ->assertJsonFragment([
            'name' => 'Guiso de garbanzos',
            'diet_category' => 'vegana',
            'base_servings' => 4,
        ])
        ->assertJsonPath('ingredients.0.pivot.unit', 'g')
        ->assertJsonPath('ingredients.0.pivot.quantity_per_serving', 200);

    $this->assertDatabaseHas('recipes', [
        'name' => 'Guiso de garbanzos',
        'user_id' => $user->id,
    ]);
});

it('creates a recipe with ingredients', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $ingredient1 = Ingredient::factory()->for($user)->create();
    $ingredient2 = Ingredient::factory()->for($user)->create();

    $payload = [
        'name' => 'Ensalada de garbanzos',
        'description' => 'Receta fresca y rica en hierro',
        'diet_category' => 'vegana',
        'base_servings' => 2,
        'ingredients' => [
            [
                'id' => $ingredient1->id,
                'unit' => 'g',
                'quantity_per_serving' => 100,
            ],
            [
                'id' => $ingredient2->id,
                'unit' => 'ml',
                'quantity_per_serving' => 50,
            ],
        ],
    ];

    $response = $this->postJson('/api/recipes', $payload);

    $response->assertCreated()
        ->assertJsonFragment([
            'name' => 'Ensalada de garbanzos',
            'diet_category' => 'vegana',
        ]);

    // Verificamos que la receta se creó en DB
    $this->assertDatabaseHas('recipes', [
        'name' => 'Ensalada de garbanzos',
        'user_id' => $user->id,
    ]);

    // Verificamos que los ingredientes se asociaron en la tabla pivote
    $this->assertDatabaseHas('recipe_ingredients', [
        'recipe_id' => Recipe::first()->id,
        'ingredient_id' => $ingredient1->id,
        'unit' => 'g',
        'quantity_per_serving' => 100,
    ]);

    $this->assertDatabaseHas('recipe_ingredients', [
        'recipe_id' => Recipe::first()->id,
        'ingredient_id' => $ingredient2->id,
        'unit' => 'ml',
        'quantity_per_serving' => 50,
    ]);
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
        $ingredient = Ingredient::factory()->create();
        $payload = [
            'name' => 'Pizza vegetariana',
            'description' => 'Con masa integral y queso',
            'diet_category' => 'vegetariana',
            'base_servings' => 2,
            'ingredients' => [
                [
                    'id' => $ingredient->id,
                    'unit' => 'g',
                    'quantity_per_serving' => 200,
                ],
            ],
        ];

        // Crear
        $response = $this->postJson('/api/recipes', $payload);
        $response->assertCreated()
            ->assertJsonFragment([
                'name' => 'Pizza vegetariana',
                'diet_category' => 'vegetariana',
            ])
            ->assertJsonPath('ingredients.0.pivot.unit', 'g')
            ->assertJsonPath('ingredients.0.pivot.quantity_per_serving', 200);

        // Ver en listado
        $this->getJson('/api/recipes')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Pizza vegetariana',
                'diet_category' => 'vegetariana',
            ]);
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
