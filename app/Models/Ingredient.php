<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_type',
        'name',
        'iron_mg_per_100g',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipes()
{
    return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
                ->withPivot('unit', 'quantity_per_serving')
                ->withTimestamps();
}
}
