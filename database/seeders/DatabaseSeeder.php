<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\Listing;
use App\Models\ModerationRecord;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use App\Models\ViewingRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Створення тестових користувачів
        $users = User::factory()->count(8)->create();

        $seller = $users->first();
        $admin = $users->last();
        $buyers = $users->slice(1, 6);

        // Призначення адміністративної ролі
        $admin->role = 'admin';
        $admin->save();

        // 2. Створення об'єктів нерухомості
        $properties = Property::factory()
            ->count(5)
            ->create();

        $listings = collect();

        foreach ($properties as $property) {

            // 3. Фотографії нерухомості
            PropertyPhoto::factory()->create([
                'property_id' => $property->id,
                'is_primary' => true,
            ]);

            PropertyPhoto::factory()->create([
                'property_id' => $property->id,
                'is_primary' => false,
            ]);

            // 4. Оголошення для кожного об'єкта
            $createdListings = Listing::factory()
                ->count(2)
                ->create([
                    'property_id' => $property->id,
                    'seller_id' => $seller->id,
                ]);

            $listings = $listings->concat($createdListings);
        }

        // 5. Вибране, заявки та сповіщення
        foreach ($buyers as $buyer) {

            // Два різні оголошення у вибраному
            foreach ($listings->random(2) as $listing) {
                Favorite::factory()->create([
                    'user_id' => $buyer->id,
                    'listing_id' => $listing->id,
                ]);
            }

            // Одна заявка на перегляд
            ViewingRequest::factory()->create([
                'buyer_id' => $buyer->id,
                'listing_id' => $listings->random()->id,
            ]);

            // Два сповіщення
            Notification::factory()->count(2)->create([
                'user_id' => $buyer->id,
            ]);
        }

        // 6. Записи модерації оголошень
        foreach ($listings as $listing) {
            ModerationRecord::factory()->create([
                'listing_id' => $listing->id,
                'admin_id' => $admin->id,
            ]);
        }
    }
}
