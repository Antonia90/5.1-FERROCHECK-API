<?php

use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;

it('validates the input data', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    // Sin recetas
    $response = $this->postJson('/api/daily-check', [
        'recipes' => [],
        'category' => 'woman_premenopausal',
    ]);
    $response->assertStatus(422);

    // Más de 8 recetas
    $recipes = Recipe::factory()->count(9)->create();
    $payload = [
        'recipes' => $recipes->map(fn($r) => ['id' => $r->id, 'servings' => 1]),
        'category' => 'woman_premenopausal',
    ];
    $response = $this->postJson('/api/daily-check', $payload);
    $response->assertStatus(422);

    // Categoría inválida
    $response = $this->postJson('/api/daily-check', [
        'recipes' => [['id' => 1, 'servings' => 1]],
        'category' => 'invalid',
    ]);
    $response->assertStatus(422);
});

it('rejects servings less than 1 in daily check', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $ingredient = Ingredient::factory()->create(['iron_mg_per_100g' => 10]);
    $recipe = Recipe::factory()->for($user)->create();
    $recipe->ingredients()->attach($ingredient->id, [
        'unit' => 'g',
        'quantity_per_serving' => 100,
    ]);

    $payload = [
        'recipes' => [
            ['id' => $recipe->id, 'servings' => 0], // ❌ invalid
        ],
        'category' => 'woman_premenopausal',
    ];

    $response = $this->postJson('/api/daily-check', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['recipes.0.servings']);
});

it('calculates total iron and compares with requirement', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    // Ingredientes
    $ingredient1 = Ingredient::factory()->create(['iron_mg_per_100g' => 10]);
    $ingredient2 = Ingredient::factory()->create(['iron_mg_per_100g' => 5]);

    // Receta 1 → aporta 10 mg por porción
    $recipe1 = Recipe::factory()->create(['base_servings' => 1]);
    $recipe1->ingredients()->attach($ingredient1->id, [
        'unit' => 'g',
        'quantity_per_serving' => 100,
    ]);

    // Receta 2 → aporta 2.5 mg por porción
    $recipe2 = Recipe::factory()->create(['base_servings' => 1]);
    $recipe2->ingredients()->attach($ingredient2->id, [
        'unit' => 'g',
        'quantity_per_serving' => 50,
    ]);

    $payload = [
        'recipes' => [
            ['id' => $recipe1->id, 'servings' => 1], // 10 mg
            ['id' => $recipe2->id, 'servings' => 2], // 2.5 * 2 = 5 mg
        ],
        'category' => 'woman_premenopausal', // requerimiento 18 mg
    ];

    $response = $this->postJson('/api/daily-check', $payload);

    $response->assertOk()
        ->assertJson([
            'total_iron_mg' => 15.0,
            'required_mg' => 18,
            'status' => 'insufficient',
            'difference_mg' => 3.0,
            'message' => 'Te faltan 3 mg de hierro para llegar al requerimiento.',
        ]);
});
