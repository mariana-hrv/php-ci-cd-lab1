<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingControllerTest extends TestCase
{
    use RefreshDatabase;

    // Перегляд оголошень із пов'язаними даними
    public function test_can_view_listings_with_relations(): void
    {
        $listing = Listing::factory()->create();

        $response = $this->getJson('/listings');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $listing->id)
            ->assertJsonPath(
                'data.0.property.id',
                $listing->property_id
            )
            ->assertJsonPath(
                'data.0.seller.id',
                $listing->seller_id
            );

        $this->getJson("/listings/{$listing->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $listing->id);
    }

    // Створення оголошення авторизованим користувачем
    public function test_user_can_create_listing(): void
    {
        $seller = User::factory()->create();
        $property = Property::factory()->create();

        $response = $this->actingAs($seller)
            ->postJson('/listings', [
                'property_id' => $property->id,
                'price' => 2500000,
                'description' => 'Квартира в центрі міста',
                'status' => 'published',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('data.seller_id', $seller->id);

        $this->assertDatabaseHas('listings', [
            'property_id' => $property->id,
            'seller_id' => $seller->id,
            'description' => 'Квартира в центрі міста',
            'status' => 'published',
        ]);
    }

    // Неавторизований користувач не може змінювати оголошення
    public function test_guest_cannot_modify_listings(): void
    {
        $listing = Listing::factory()->create();

        $this->postJson('/listings', [])
            ->assertStatus(401);

        $this->putJson("/listings/{$listing->id}", [
            'price' => 3000000,
        ])->assertStatus(401);

        $this->deleteJson("/listings/{$listing->id}")
            ->assertStatus(401);
    }

    // Продавець може редагувати власне оголошення
    public function test_seller_can_update_own_listing(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $response = $this->actingAs($seller)
            ->putJson("/listings/{$listing->id}", [
                'description' => 'Оновлений опис оголошення',
                'status' => 'published',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'description' => 'Оновлений опис оголошення',
            'status' => 'published',
        ]);
    }

    // Чужий продавець не може змінювати оголошення
    public function test_user_cannot_modify_another_sellers_listing(): void
    {
        $seller = User::factory()->create();
        $otherUser = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($otherUser)
            ->putJson("/listings/{$listing->id}", [
                'description' => 'Чужа зміна',
            ])->assertStatus(403);

        $this->actingAs($otherUser)
            ->deleteJson("/listings/{$listing->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'seller_id' => $seller->id,
        ]);
    }

    // Продавець може видаляти власне оголошення
    public function test_seller_can_delete_own_listing(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($seller)
            ->deleteJson("/listings/{$listing->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('listings', [
            'id' => $listing->id,
        ]);
    }

    // Перевірка некоректних даних
    public function test_creation_rejects_invalid_data(): void
    {
        $seller = User::factory()->create();

        $response = $this->actingAs($seller)
            ->postJson('/listings', [
                'property_id' => 999999,
                'price' => -100,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'property_id',
                'price',
            ]);

        $this->assertDatabaseCount('listings', 0);
    }
}
