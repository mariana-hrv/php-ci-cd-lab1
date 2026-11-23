<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),

            'seller_id' => User::factory(),

            'price' => fake()->randomFloat(2, 100000, 15000000),

            'description' => fake()->sentence(12),

            'status' => fake()->randomElement([
                'draft',
                'published',
                'archived',
            ]),
        ];
    }
}
