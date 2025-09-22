<?php

use App\Models\User;
use App\Models\Ingredient;
use App\Models\Recipe;

describe('Recipe CRUD', function () {

    it('lists all recipes for authenticated user', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        Recipe::factory()->count(2)->for($user)->create();

        $response = $this->getJson('/api/recipes');

        $response->assertOk()
                 ->assertJsonCount(2);
    });

    it('shows a single recipe', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $recipe = Recipe::factory()->for($user)->create([
            'title' => 'Ensalada de Espinaca',
            'instructions' => 'Mezclar ingredientes y servir',
        ]);

        $response = $this->getJson("/api/recipes/{$recipe->id}");

        $response->assertOk()
                 ->assertJsonFragment([
                     'title' => 'Ensalada de Espinaca',
                 ]);
    });

    it('creates a new recipe with ingredients', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $ingredients = Ingredient::factory()->count(2)->create();

        $payload = [
            'title' => 'Sopa de lentejas',
            'instructions' => 'Cocinar las lentejas con verduras',
            'ingredients' => $ingredients->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/recipes', $payload);

        $response->assertCreated()
                 ->assertJsonFragment(['title' => 'Sopa de lentejas']);

        $this->assertDatabaseHas('recipes', [
            'title' => 'Sopa de lentejas',
            'user_id' => $user->id,
        ]);
    });

    it('updates a recipe', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $recipe = Recipe::factory()->for($user)->create([
            'title' => 'Tarta de manzana',
            'instructions' => 'Cortar manzanas',
        ]);

        $payload = [
            'title' => 'Tarta de manzana y canela',
            'instructions' => 'Cortar manzanas y agregar canela',
        ];

        $response = $this->putJson("/api/recipes/{$recipe->id}", $payload);

        $response->assertOk()
                 ->assertJsonFragment($payload);
    });

    it('deletes a recipe', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $recipe = Recipe::factory()->for($user)->create();

        $response = $this->deleteJson("/api/recipes/{$recipe->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('recipes', [
            'id' => $recipe->id,
        ]);
    });
});

describe('Recipe permissions', function () {

    it('prevents unauthenticated users from accessing recipes', function () {
        $response = $this->getJson('/api/recipes');
        $response->assertUnauthorized();
    });

    it('allows admin to manage all recipes', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'api');

        $recipe = Recipe::factory()->create();

        $payload = ['title' => 'Nuevo titulo', 'instructions' => 'Nuevas instrucciones'];
        $this->putJson("/api/recipes/{$recipe->id}", $payload)
             ->assertOk()
             ->assertJsonFragment($payload);

        $this->deleteJson("/api/recipes/{$recipe->id}")
             ->assertNoContent();
    });

    it('allows user to manage only their own recipes', function () {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $otherUser = User::factory()->create();
        $recipe = Recipe::factory()->for($otherUser)->create();

        $payload = ['title' => 'Hackeando receta', 'instructions' => 'No deberia'];
        $this->putJson("/api/recipes/{$recipe->id}", $payload)
             ->assertForbidden();

        $this->deleteJson("/api/recipes/{$recipe->id}")
             ->assertForbidden();
    });
});
