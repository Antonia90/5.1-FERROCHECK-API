<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            [
                'ingredient_type' => 'verdura',
                'name' => 'Espinaca',
                'iron_mg_per_100g' => 2.7,
                'user_id' => 1,
            ],
            [
                'ingredient_type' => 'fruta',
                'name' => 'Manzana',
                'iron_mg_per_100g' => 0.1,
                'user_id' => 1,
            ],
            [
                'ingredient_type' => 'proteina',
                'name' => 'Lentejas',
                'iron_mg_per_100g' => 3.3,
                'user_id' => 1,
            ],
        ];

        foreach ($ingredients as $ingredient) {
            Ingredient::create($ingredient);
        }

        Ingredient::factory()->count(10)->create([
            'user_id' => 1,
        ]);
    }
}

