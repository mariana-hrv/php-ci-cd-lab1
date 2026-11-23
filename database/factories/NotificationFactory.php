<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'message' => fake()->sentence(10),

            'type' => fake()->randomElement([
                'viewing_request',
                'listing_status',
                'moderation',
            ]),

            'is_read' => fake()->boolean(30),
        ];
    }
}
