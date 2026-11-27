<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_only_published_listings_on_web(): void
    {
        $published = Listing::factory()->create([
            'status' => 'published',
        ]);

        $draft = Listing::factory()->create([
            'status' => 'draft',
        ]);

        $archived = Listing::factory()->create([
            'status' => 'archived',
        ]);

        $this->getJson('/listings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id);

        $this->get("/listings/{$draft->id}")
            ->assertForbidden();

        $this->get("/listings/{$archived->id}")
            ->assertForbidden();
    }

    public function test_guest_sees_only_published_listings_in_api(): void
    {
        $published = Listing::factory()->create([
            'status' => 'published',
        ]);

        $draft = Listing::factory()->create([
            'status' => 'draft',
        ]);

        $this->getJson('/api/v1/listings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id);

        $this->getJson("/api/v1/listings/{$draft->id}")
            ->assertForbidden();
    }

    public function test_seller_can_view_own_draft(): void
    {
        $seller = User::factory()->create();

        $own = Listing::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'draft',
        ]);

        $other = Listing::factory()->create([
            'status' => 'draft',
        ]);

        $this->actingAs($seller);

        $this->getJson('/listings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);

        $this->get("/listings/{$own->id}")
            ->assertOk();

        $this->get("/listings/{$other->id}")
            ->assertForbidden();
    }

    public function test_sanctum_token_allows_seller_to_view_own_draft(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'draft',
        ]);

        $token = $seller->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/v1/listings/{$listing->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $listing->id);
    }

    public function test_admin_can_view_all_listings(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Listing::factory()->create(['status' => 'published']);
        Listing::factory()->create(['status' => 'draft']);
        Listing::factory()->create(['status' => 'archived']);

        $this->actingAs($admin)
            ->getJson('/listings')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
