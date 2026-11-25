<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiListingSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_listing(): void
    {
        $response = $this->postJson('/api/v1/listings', []);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('listings', 0);
    }

    public function test_user_cannot_delete_another_users_listing(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson(
            "/api/v1/listings/{$listing->id}"
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'seller_id' => $owner->id,
        ]);
    }

    public function test_owner_can_delete_own_listing(): void
    {
        $owner = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson(
            "/api/v1/listings/{$listing->id}"
        );

        $response->assertOk();

        $this->assertDatabaseMissing('listings', [
            'id' => $listing->id,
        ]);
    }
}
