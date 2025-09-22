<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;

class DailyCheckController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipes' => 'required|array|min:1|max:8',
            'recipes.*.id' => 'required|exists:recipes,id',
            'recipes.*.servings' => 'required|integer|min:1',
            'category' => 'required|in:woman_premenopausal, woman_postmenstrual, man_adult,pregnant',
        ]);

        $requirements = [
            'woman_premenopausal' => 18,
            'woman_postmenstrual' => 8,
            'man_adult' => 8,
            'pregnant' => 27,
        ];

        $totalIron = 0;

        foreach ($validated['recipes'] as $recipeData) {
            $recipe = Recipe::with('ingredients')->find($recipeData['id']);
            foreach ($recipe->ingredients as $ingredient) {
                $ironPerServing = ($ingredient->pivot->quantity_per_serving * $ingredient->iron_mg_per_100g) / 100;
                $totalIron += $ironPerServing * $recipeData['servings'];
            }
        }

        $required = $requirements[$validated['category']];
        $difference = round($totalIron - $required, 2);

        $status = $difference >= 0 ? 'sufficient' : 'insufficient';

        $message = $status === 'sufficient'
            ? 'Tu consumo de hierro cubre el requerimiento diario 👏'
            : "Te faltan " . abs($difference) . " mg de hierro para llegar al requerimiento.";

        return response()->json([
            'total_iron_mg' => round($totalIron, 2),
            'required_mg' => $required,
            'status' => $status,
            'difference_mg' => abs($difference),
            'message' => $message,
        ], 200);
    }
}
