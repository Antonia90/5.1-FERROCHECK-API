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
        $query = Recipe::query();

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
        ]);

        $recipe = Recipe::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return response()->json($recipe, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Recipe $recipe)
    {
        return response()->json($recipe, 200);
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
        ]);

        $recipe->update($validated);

        return response()->json($recipe, 200);
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
