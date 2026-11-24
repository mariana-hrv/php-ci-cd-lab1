<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    // Гість не може працювати зі сповіщеннями
    public function test_guest_cannot_access_notifications(): void
    {
        $notification = Notification::factory()->create();

        $this->getJson('/notifications')
            ->assertStatus(401);

        $this->getJson("/notifications/{$notification->id}")
            ->assertStatus(401);

        $this->patchJson("/notifications/{$notification->id}/read")
            ->assertStatus(401);

        $this->deleteJson("/notifications/{$notification->id}")
            ->assertStatus(401);
    }

    // Користувач бачить лише власні сповіщення
    public function test_user_can_view_own_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownNotification = Notification::factory()->create([
            'user_id' => $user->id,
        ]);

        Notification::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/notifications');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownNotification->id);
    }

    // Перегляд конкретного власного сповіщення
    public function test_user_can_view_single_notification(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'message' => 'Вашу заявку підтверджено.',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath(
                'data.message',
                'Вашу заявку підтверджено.'
            );
    }

    // Позначення сповіщення як прочитаного
    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)
            ->patchJson("/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.is_read', true);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'is_read' => 1,
        ]);
    }

    // Користувач не може працювати з чужим сповіщенням
    public function test_user_cannot_access_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $owner->id,
            'is_read' => false,
        ]);

        $this->actingAs($otherUser)
            ->getJson("/notifications/{$notification->id}")
            ->assertStatus(404);

        $this->patchJson("/notifications/{$notification->id}/read")
            ->assertStatus(404);

        $this->deleteJson("/notifications/{$notification->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $owner->id,
            'is_read' => 0,
        ]);
    }

    // Користувач може видалити власне сповіщення
    public function test_user_can_delete_own_notification(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    // Неіснуюче сповіщення повертає 404
    public function test_nonexistent_notification_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/notifications/999999')
            ->assertStatus(404);

        $this->patchJson('/notifications/999999/read')
            ->assertStatus(404);

        $this->deleteJson('/notifications/999999')
            ->assertStatus(404);
    }
}
