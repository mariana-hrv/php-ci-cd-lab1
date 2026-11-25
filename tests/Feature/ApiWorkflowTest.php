<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_and_seller_can_process_viewing_request(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $otherUser = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson('/api/v1/viewing-requests', [
            'listing_id' => $listing->id,
            'preferred_date' => now()->addDays(3)->format('Y-m-d'),
            'preferred_time' => '14:30',
            'comment' => 'Test viewing request',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.buyer_id', $buyer->id)
            ->assertJsonPath('data.status', 'pending');

        $id = $response->json('data.id');

        Sanctum::actingAs($otherUser);

        $this->patchJson("/api/v1/viewing-requests/{$id}/decide", [
            'status' => 'confirmed',
        ])->assertForbidden();

        Sanctum::actingAs($seller);

        $this->patchJson("/api/v1/viewing-requests/{$id}/decide", [
            'status' => 'confirmed',
        ])->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'type' => 'viewing_request',
        ]);

        Sanctum::actingAs($buyer);

        $this->putJson("/api/v1/viewing-requests/{$id}", [
            'preferred_time' => '16:00',
        ])->assertStatus(409);
    }

    public function test_user_can_manage_only_own_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = $user->propertyNotifications()->create([
            'type' => 'viewing_request',
            'message' => 'Test notification',
            'is_read' => false,
        ]);

        Sanctum::actingAs($otherUser);

        $this->getJson("/api/v1/notifications/{$notification->id}")
            ->assertForbidden();

        $this->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertForbidden();

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->deleteJson("/api/v1/notifications/{$notification->id}")
            ->assertOk();

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_only_admin_can_moderate_listing(): void
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $listing = Listing::factory()->create([
            'status' => 'draft',
        ]);

        $data = [
            'listing_id' => $listing->id,
            'decision' => 'approved',
            'comment' => 'Test moderation',
        ];

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/moderation-records', $data)
            ->assertForbidden();

        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/moderation-records', $data)
            ->assertCreated()
            ->assertJsonPath('data.admin_id', $admin->id);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'status' => 'published',
        ]);
    }

    public function test_user_cannot_change_role_or_access_users_list(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/users')
            ->assertForbidden();

        $this->putJson('/api/v1/profile', [
            'name' => 'Updated API User',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated API User')
            ->assertJsonPath('data.role', 'user');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
            'role' => 'user',
        ]);
    }
}
