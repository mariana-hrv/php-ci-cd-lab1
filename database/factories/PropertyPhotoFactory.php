<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyPhoto>
 */
class PropertyPhotoFactory extends Factory
{
    protected $model = PropertyPhoto::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),

            'file_path' => 'properties/'.
                fake()->uuid().'.jpg',

            'is_primary' => false,
        ];
    }
}
