<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class IngredientController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(\App\Models\Ingredient::class, 'ingredient');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Ingredient::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ingredient_type' => 'required|in:verdura,fruta,proteina,lacteo,condimento,otro',
            'name' => 'required|string|max:255',
            'iron_mg_per_100g' => 'required|numeric|min:0',
        ]);

        $ingredient = Ingredient::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return response()->json($ingredient, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ingredient = Ingredient::findOrFail($id);
        return response()->json($ingredient, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ingredient = Ingredient::findOrFail($id);

        $validated = $request->validate([
            'ingredient_type' => 'required|in:verdura,fruta,proteina,lacteo,condimento,otro',
            'name' => 'required|string|max:255',
            'iron_mg_per_100g' => 'required|numeric|min:0',
        ]);

        $ingredient->update($validated);

        return response()->json($ingredient, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();

        return response()->noContent();
    }
}
