<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_update_listing(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/listings', [
            'property_id' => $property->id,
            'seller_id' => $otherUser->id,
            'price' => 95000,
            'description' => 'Test listing',
            'status' => 'draft',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.seller_id', $user->id);

        $id = $response->json('data.id');

        $this->putJson("/api/v1/listings/{$id}", [
            'price' => 98000,
            'status' => 'published',
        ])->assertOk()
            ->assertJsonPath('data.price', 98000)
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('listings', [
            'id' => $id,
            'seller_id' => $user->id,
            'price' => 98000,
        ]);
    }

    public function test_user_can_create_property_but_cannot_edit_it(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $data = [
            'property_type' => 'apartment',
            'city' => 'Ivano-Frankivsk',
            'address' => 'Shevchenka Street, 25',
            'area' => 75.5,
            'rooms' => 3,
        ];

        $response = $this->postJson('/api/v1/properties', $data);

        $response->assertCreated();

        $id = $response->json('data.id');

        $this->putJson("/api/v1/properties/{$id}", [
            ...$data,
            'area' => 100,
        ])->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id' => $id,
            'area' => 75.5,
        ]);
    }

    public function test_only_seller_can_manage_property_photos(): void
    {
        $seller = User::factory()->create();
        $otherUser = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $data = [
            'property_id' => $listing->property_id,
            'file_path' => 'properties/test.jpg',
            'is_primary' => true,
        ];

        Sanctum::actingAs($otherUser);

        $this->postJson('/api/v1/photos', $data)
            ->assertForbidden();

        Sanctum::actingAs($seller);

        $response = $this->postJson('/api/v1/photos', $data);

        $response->assertCreated();

        $id = $response->json('data.id');

        $this->assertDatabaseHas('property_photos', [
            'id' => $id,
            'property_id' => $listing->property_id,
        ]);

        Sanctum::actingAs($otherUser);

        $this->deleteJson("/api/v1/photos/{$id}")
            ->assertForbidden();

        Sanctum::actingAs($seller);

        $this->deleteJson("/api/v1/photos/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('property_photos', [
            'id' => $id,
        ]);
    }

    public function test_user_can_manage_only_own_favorites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $listing = Listing::factory()->create();
        $otherListing = Listing::factory()->create();

        $otherUser->favoriteListings()->attach($otherListing->id);

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/favorites', [
            'listing_ids' => [$listing->id],
        ])->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.listing_id', $listing->id);

        $this->putJson('/api/v1/favorites', [
            'listing_ids' => [],
        ])->assertOk()
            ->assertJsonCount(0, 'data');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $otherUser->id,
            'listing_id' => $otherListing->id,
        ]);
    }
}
