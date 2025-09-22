<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Recipe::class, 'recipe');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Recipe::with('ingredients');

        if ($request->has('diet_category')) {
            $query->where('diet_category', $request->diet_category);
        }

        return response()->json($query->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'diet_category' => 'required|in:vegana,vegetariana,omnivora',
            'base_servings' => 'required|integer|min:1',
            'ingredients' => 'array|required',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.unit' => 'required|string|max:50',
            'ingredients.*.quantity_per_serving' => 'required|numeric|min:0',
        ]);

        // $recipe = Recipe::create([
        //     ...$validated,
        //     'user_id' => Auth::id(),
        // ]);

        $recipe = Recipe::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'diet_category' => $validated['diet_category'],
            'base_servings' => $validated['base_servings'],
            'user_id' => $request->user()->id,
        ]);
        // Guardamos ingredientes en la tabla pivote
        foreach ($validated['ingredients'] as $ingredientData) {
            $recipe->ingredients()->attach($ingredientData['id'], [
                'unit' => $ingredientData['unit'],
                'quantity_per_serving' => $ingredientData['quantity_per_serving'],
            ]);
        }
        return response()->json($recipe->load('ingredients'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Recipe $recipe)
    {
        return response()->json($recipe->Load('ingredients'), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'diet_category' => 'required|in:vegana,vegetariana,omnivora',
            'base_servings' => 'required|integer|min:1',
            'ingredients' => 'nullable|array',
            'ingredients.*.id' => 'required_with:ingredients|exists:ingredients,id',
            'ingredients.*.unit' => 'required_with:ingredients|string|max:50',
            'ingredients.*.quantity_per_serving' => 'required_with:ingredients|numeric|min:0',
        ]);

        //$recipe->update($validated);
        $recipe->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'diet_category' => $validated['diet_category'],
            'base_servings' => $validated['base_servings'],
        ]);

        if (isset($validated['ingredients'])) {
            // Sincronizamos los ingredientes (borramos lo viejo y guardamos lo nuevo)
            $syncData = [];
            foreach ($validated['ingredients'] as $ingredientData) {
                $syncData[$ingredientData['id']] = [
                    'unit' => $ingredientData['unit'],
                    'quantity_per_serving' => $ingredientData['quantity_per_serving'],
                ];
            }
            $recipe->ingredients()->sync($syncData);
        }
        return response()->json($recipe->load('ingredients'), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return response()->noContent();
    }
}
