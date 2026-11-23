<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ModerationRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModerationRecord>
 */
class ModerationRecordFactory extends Factory
{
    protected $model = ModerationRecord::class;

    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),

            'admin_id' => User::factory(),

            'decision' => fake()->randomElement([
                'approved',
                'rejected',
            ]),

            'comment' => fake()->optional(0.7)->sentence(),
        ];
    }
}
