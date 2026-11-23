<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Listing;
use App\Models\User;
use App\Models\ViewingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViewingRequest>
 */
class ViewingRequestFactory extends Factory
{
    protected $model = ViewingRequest::class;

    public function definition(): array
    {
        return [
            'buyer_id' => User::factory(),

            'listing_id' => Listing::factory(),

            'preferred_date' => fake()
                ->dateTimeBetween('+1 day', '+30 days')
                ->format('Y-m-d'),

            'preferred_time' => fake()->randomElement([
                '10:00:00',
                '12:00:00',
                '14:00:00',
                '16:00:00',
                '18:00:00',
            ]),

            'status' => fake()->randomElement([
                'pending',
                'confirmed',
                'rejected',
                'cancelled',
            ]),

            'comment' => fake()->optional(0.7)->sentence(),
        ];
    }
}
