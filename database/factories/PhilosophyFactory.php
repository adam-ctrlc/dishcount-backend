<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PhilosophyFactory extends Factory
{
    public function definition(): array
    {
        $types = ['about', 'mission', 'vision'];
        $type = fake()->randomElement($types);
        
        $descriptions = [
            'about' => 'We are dedicated to providing exceptional dining experiences through innovative cuisine and outstanding service.',
            'mission' => 'To create extraordinary culinary experiences that bring people together, celebrating the art of fine dining through innovative cuisine, exceptional service, and warm hospitality.',
            'vision' => 'To be the premier destination for culinary excellence, setting new standards in the restaurant industry through sustainable practices and innovative flavors.'
        ];

        return [
            'description' => $descriptions[$type] ?? fake()->paragraph(),
            'type' => $type,
        ];
    }

    public function about()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'about',
                'description' => 'We are dedicated to providing exceptional dining experiences through innovative cuisine and outstanding service.',
            ];
        });
    }

    public function mission()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'mission',
                'description' => 'To create extraordinary culinary experiences that bring people together, celebrating the art of fine dining through innovative cuisine, exceptional service, and warm hospitality.',
            ];
        });
    }

    public function vision()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'vision',
                'description' => 'To be the premier destination for culinary excellence, setting new standards in the restaurant industry through sustainable practices and innovative flavors.',
            ];
        });
    }
}