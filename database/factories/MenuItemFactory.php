<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' =>Category::factory(),
            'name'=>ucfirst(fake()->words(2,true)),
            'description'=>fake()->sentence(10),
            'price'=>fake()->randomFloat(2,1000,15000),
            'image'=>null,
            'is_available'=>fake()->boolean(85),

        ];
    }
}
