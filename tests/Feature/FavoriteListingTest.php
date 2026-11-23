<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteListingTest extends TestCase
{
    use RefreshDatabase;

    // Неавторизований користувач не має доступу
    public function test_guest_cannot_access_favorites(): void
    {
        $this->getJson('/favorites')
            ->assertStatus(401);

        $this->putJson('/favorites', [
            'listing_ids' => [],
        ])->assertStatus(401);
    }

    // Отримання вибраних оголошень із нерухомістю
    public function test_user_can_view_favorites(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create();

        $user->favoriteListings()->attach(
            $listing->id,
            ['note' => 'Цікаве оголошення']
        );

        $response = $this->actingAs($user)
            ->getJson('/favorites');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $listing->id)
            ->assertJsonPath(
                'data.0.property.id',
                $listing->property_id
            )
            ->assertJsonPath(
                'data.0.pivot.note',
                'Цікаве оголошення'
            );
    }

    // Метод sync() оновлює проміжну таблицю
    public function test_sync_updates_favorites(): void
    {
        $user = User::factory()->create();

        $listing1 = Listing::factory()->create();
        $listing2 = Listing::factory()->create();
        $listing3 = Listing::factory()->create();

        // Початковий список вибраного
        $user->favoriteListings()->attach([
            $listing1->id,
            $listing2->id,
        ]);

        // Заміна списку через HTTP-запит
        $response = $this->actingAs($user)
            ->putJson('/favorites', [
                'listing_ids' => [
                    $listing2->id,
                    $listing3->id,
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data');

        // Старий зв'язок видалено
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'listing_id' => $listing1->id,
        ]);

        // Потрібний зв'язок залишився
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'listing_id' => $listing2->id,
        ]);

        // Новий зв'язок створено
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'listing_id' => $listing3->id,
        ]);

        $this->assertDatabaseCount('favorites', 2);
    }

    // Перевірка неіснуючого оголошення
    public function test_sync_rejects_invalid_listing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/favorites', [
                'listing_ids' => [999999],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'listing_ids.0',
            ]);

        $this->assertDatabaseCount('favorites', 0);
    }

    // Перевірка очищення списку вибраного
    public function test_sync_can_clear_favorites(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create();

        $user->favoriteListings()->attach($listing->id);

        $response = $this->actingAs($user)
            ->putJson('/favorites', [
                'listing_ids' => [],
            ]);

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');

        $this->assertDatabaseCount('favorites', 0);
    }
}
