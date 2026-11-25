<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebListingSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/listings/create');

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_open_another_users_edit_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->get("/listings/{$listing->id}/edit");

        $response->assertForbidden();
    }

    public function test_owner_can_open_own_edit_page(): void
    {
        $owner = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->get("/listings/{$listing->id}/edit");

        $response->assertOk();
    }
}
