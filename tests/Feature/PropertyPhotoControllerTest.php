<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyPhotoControllerTest extends TestCase
{
    use RefreshDatabase;

    // Публічний перегляд фотографій нерухомості
    public function test_can_view_property_photos(): void
    {
        $property = Property::factory()->create();

        $primaryPhoto = PropertyPhoto::factory()->create([
            'property_id' => $property->id,
            'is_primary' => true,
        ]);

        PropertyPhoto::factory()->create([
            'property_id' => $property->id,
            'is_primary' => false,
        ]);

        $response = $this->getJson(
            "/properties/{$property->id}/photos"
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $primaryPhoto->id);
    }

    // Продавець може додати фотографію
    public function test_seller_can_add_photo(): void
    {
        $seller = User::factory()->create();
        $property = Property::factory()->create();

        Listing::factory()->create([
            'property_id' => $property->id,
            'seller_id' => $seller->id,
        ]);

        $response = $this->actingAs($seller)
            ->postJson('/property-photos', [
                'property_id' => $property->id,
                'file_path' => 'properties/apartment.jpg',
                'is_primary' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('data.property_id', $property->id)
            ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('property_photos', [
            'property_id' => $property->id,
            'file_path' => 'properties/apartment.jpg',
            'is_primary' => 1,
        ]);
    }

    // Гість не може додавати або видаляти фотографії
    public function test_guest_cannot_modify_photos(): void
    {
        $photo = PropertyPhoto::factory()->create();

        $this->postJson('/property-photos', [
            'property_id' => $photo->property_id,
            'file_path' => 'properties/test.jpg',
        ])->assertStatus(401);

        $this->deleteJson("/property-photos/{$photo->id}")
            ->assertStatus(401);

        $this->assertDatabaseCount('property_photos', 1);
    }

    // Чужий користувач не може додавати фотографії
    public function test_other_user_cannot_add_photo(): void
    {
        $seller = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();

        Listing::factory()->create([
            'property_id' => $property->id,
            'seller_id' => $seller->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->postJson('/property-photos', [
                'property_id' => $property->id,
                'file_path' => 'properties/other.jpg',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseCount('property_photos', 0);
    }

    // Продавець може видалити фотографію свого об'єкта
    public function test_seller_can_delete_photo(): void
    {
        $seller = User::factory()->create();
        $property = Property::factory()->create();

        Listing::factory()->create([
            'property_id' => $property->id,
            'seller_id' => $seller->id,
        ]);

        $photo = PropertyPhoto::factory()->create([
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($seller)
            ->deleteJson("/property-photos/{$photo->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('property_photos', [
            'id' => $photo->id,
        ]);
    }

    // Чужий користувач не може видалити фотографію
    public function test_other_user_cannot_delete_photo(): void
    {
        $seller = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();

        Listing::factory()->create([
            'property_id' => $property->id,
            'seller_id' => $seller->id,
        ]);

        $photo = PropertyPhoto::factory()->create([
            'property_id' => $property->id,
        ]);

        $this->actingAs($otherUser)
            ->deleteJson("/property-photos/{$photo->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('property_photos', [
            'id' => $photo->id,
        ]);
    }

    // Перевірка некоректних даних
    public function test_photo_creation_rejects_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/property-photos', [
                'property_id' => 999999,
                'file_path' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'property_id',
                'file_path',
            ]);

        $this->assertDatabaseCount('property_photos', 0);
    }
}
