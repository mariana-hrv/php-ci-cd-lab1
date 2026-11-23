<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySearchTest extends TestCase
{
    use RefreshDatabase;

    // Перевірка пошуку за трьома параметрами
    public function test_search_filters_properties(): void
    {
        $matching = Property::factory()->create([
            'city' => 'Ivano-Frankivsk',
            'property_type' => 'apartment',
            'rooms' => 4,
        ]);

        Property::factory()->create([
            'city' => 'Lviv',
            'property_type' => 'apartment',
            'rooms' => 4,
        ]);

        Property::factory()->create([
            'city' => 'Ivano-Frankivsk',
            'property_type' => 'house',
            'rooms' => 4,
        ]);

        Property::factory()->create([
            'city' => 'Ivano-Frankivsk',
            'property_type' => 'apartment',
            'rooms' => 2,
        ]);

        $response = $this->getJson(
            '/properties/search?city=Ivano-Frankivsk&propertyType=apartment&rooms=4'
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id);
    }

    // Перевірка пошуку без фільтрів
    public function test_search_returns_all_properties(): void
    {
        Property::factory()->count(3)->create();

        $response = $this->getJson('/properties/search');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    // Перевірка пошуку без результатів
    public function test_search_returns_empty_array(): void
    {
        Property::factory()->create([
            'city' => 'Lviv',
        ]);

        $response = $this->getJson(
            '/properties/search?city=Odesa'
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertExactJson([
                'status' => 'success',
                'data' => [],
            ]);
    }

    // Перевірка завантаження пов'язаних даних
    public function test_search_includes_related_records(): void
    {
        $property = Property::factory()->create();

        $listing = Listing::factory()->create([
            'property_id' => $property->id,
        ]);

        $photo = PropertyPhoto::factory()->create([
            'property_id' => $property->id,
        ]);

        $response = $this->getJson('/properties/search');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $property->id)
            ->assertJsonCount(1, 'data.0.listings')
            ->assertJsonCount(1, 'data.0.photos')
            ->assertJsonPath(
                'data.0.listings.0.id',
                $listing->id
            )
            ->assertJsonPath(
                'data.0.photos.0.id',
                $photo->id
            );
    }
}
