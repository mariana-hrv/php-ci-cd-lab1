<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListingCrudRefactoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_crud_across_web_and_api(): void
    {
        $seller = User::factory()->create();
        $property = Property::factory()->create();

        Sanctum::actingAs($seller);

        // Create: створення через API
        $response = $this->postJson('/api/v1/listings', [
            'property_id' => $property->id,
            'price' => 2500000,
            'description' => 'Test listing',
            'status' => 'draft',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.seller_id', $seller->id)
            ->assertJsonPath('data.property.id', $property->id);

        $id = $response->json('data.id');

        // Read: перегляд через Web
        $this->actingAs($seller, 'web')
            ->get("/listings/{$id}")
            ->assertOk();

        // Update: редагування через Web
        $this->put("/listings/{$id}", [
            'price' => 2700000,
            'description' => 'Updated listing',
            'status' => 'published',
        ])->assertRedirect(route('listings.index'));

        // Перевірка оновлених даних через API
        $this->getJson("/api/v1/listings/{$id}")
            ->assertOk()
            ->assertJsonPath('data.price', 2700000)
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.description', 'Updated listing');

        Sanctum::actingAs($seller);

        // Перевірка валідації
        $this->putJson("/api/v1/listings/{$id}", [
            'price' => -1000,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);

        $this->assertDatabaseHas('listings', [
            'id' => $id,
            'price' => 2700000,
        ]);

        // Delete: видалення через API
        $this->deleteJson("/api/v1/listings/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('listings', [
            'id' => $id,
        ]);

        $this->getJson("/api/v1/listings/{$id}")
            ->assertNotFound();
    }
}
