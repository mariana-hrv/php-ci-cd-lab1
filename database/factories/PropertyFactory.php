<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'property_type' => fake()->randomElement([
                'apartment',
                'house',
                'commercial',
            ]),

            'city' => fake()->randomElement([
                'Ivano-Frankivsk',
                'Lviv',
                'Kyiv',
                'Ternopil',
                'Chernivtsi',
            ]),

            'address' => fake()->streetAddress(),

            'area' => fake()->randomFloat(2, 25, 250),

            'rooms' => fake()->numberBetween(1, 6),
        ];
    }
}
