<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ModerationRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationRecordControllerTest extends TestCase
{
    use RefreshDatabase;

    // Створення адміністратора для тестування
    private function createAdmin(): User
    {
        $admin = User::factory()->create();

        $admin->role = 'admin';
        $admin->save();

        return $admin;
    }

    // Гість не має доступу до модерації
    public function test_guest_cannot_access_moderation(): void
    {
        $record = ModerationRecord::factory()->create();

        $this->getJson('/moderation-records')
            ->assertStatus(401);

        $this->getJson(
            "/moderation-records/{$record->id}"
        )->assertStatus(401);

        $this->postJson('/moderation-records', [
            'listing_id' => $record->listing_id,
            'decision' => 'approved',
        ])->assertStatus(401);
    }

    // Звичайний користувач не може модерувати оголошення
    public function test_regular_user_cannot_moderate(): void
    {
        $user = User::factory()->create();

        $listing = Listing::factory()->create([
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->getJson('/moderation-records')
            ->assertStatus(403);

        $this->postJson('/moderation-records', [
            'listing_id' => $listing->id,
            'decision' => 'approved',
        ])->assertStatus(403);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseCount('moderation_records', 0);
    }

    // Адміністратор бачить історію модерації
    public function test_admin_can_view_moderation_records(): void
    {
        $admin = $this->createAdmin();

        $listing = Listing::factory()->create();

        $record = ModerationRecord::factory()->create([
            'listing_id' => $listing->id,
            'admin_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/moderation-records');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $record->id)
            ->assertJsonPath(
                'data.0.listing.id',
                $listing->id
            )
            ->assertJsonPath(
                'data.0.admin.id',
                $admin->id
            );
    }

    // Адміністратор може переглянути конкретне рішення
    public function test_admin_can_view_single_record(): void
    {
        $admin = $this->createAdmin();

        $record = ModerationRecord::factory()->create([
            'admin_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/moderation-records/{$record->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $record->id)
            ->assertJsonPath(
                'data.admin.id',
                $admin->id
            );
    }

    // Адміністратор схвалює оголошення
    public function test_admin_can_approve_listing(): void
    {
        $admin = $this->createAdmin();

        $listing = Listing::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/moderation-records', [
                'listing_id' => $listing->id,
                'decision' => 'approved',
                'comment' => 'Оголошення перевірено.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('data.decision', 'approved')
            ->assertJsonPath(
                'data.listing.status',
                'published'
            );

        $this->assertDatabaseHas('moderation_records', [
            'listing_id' => $listing->id,
            'admin_id' => $admin->id,
            'decision' => 'approved',
        ]);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'status' => 'published',
        ]);
    }

    // Адміністратор відхиляє оголошення
    public function test_admin_can_reject_listing(): void
    {
        $admin = $this->createAdmin();

        $listing = Listing::factory()->create([
            'status' => 'published',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/moderation-records', [
                'listing_id' => $listing->id,
                'decision' => 'rejected',
                'comment' => 'Недостатньо інформації.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('data.decision', 'rejected')
            ->assertJsonPath(
                'data.listing.status',
                'draft'
            );

        $this->assertDatabaseHas('moderation_records', [
            'listing_id' => $listing->id,
            'admin_id' => $admin->id,
            'decision' => 'rejected',
        ]);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'status' => 'draft',
        ]);
    }

    // Некоректне рішення модерації відхиляється
    public function test_moderation_rejects_invalid_data(): void
    {
        $admin = $this->createAdmin();

        $listing = Listing::factory()->create([
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->postJson('/moderation-records', [
                'listing_id' => $listing->id,
                'decision' => 'unknown',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['decision']);

        $this->postJson('/moderation-records', [
            'listing_id' => 999999,
            'decision' => 'approved',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['listing_id']);

        $this->assertDatabaseCount('moderation_records', 0);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'status' => 'draft',
        ]);
    }

    // Неіснуючий запис модерації повертає 404
    public function test_nonexistent_moderation_record_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->getJson('/moderation-records/999999')
            ->assertStatus(404);
    }
}
