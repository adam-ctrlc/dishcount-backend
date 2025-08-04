<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = [
            'Pizza', 'Burgers', 'Pasta', 'Salads', 'Desserts', 'Drinks',
            'Breakfast', 'Lunch', 'Dinner', 'Filipino', 'Chinese', 'Japanese',
            'Korean', 'Thai', 'Vietnamese', 'Indian', 'Malaysian'
        ];

        return [
            'name' => fake()->unique()->randomElement($categories),
        ];
    }
}