<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    public function definition(): array
    {
        return [
            'ingredient_type' => $this->faker->randomElement([
                'verdura', 'fruta', 'proteina', 'lacteo', 'condimento', 'otro'
            ]),
            'name' => $this->faker->word(),
            'iron_mg_per_100g' => $this->faker->randomFloat(2, 0, 10),
            'user_id' => User::factory(),
        ];
    }
}
