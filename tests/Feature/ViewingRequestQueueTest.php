<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendViewingRequestNotification;
use App\Models\Listing;
use App\Models\User;
use App\Models\ViewingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ViewingRequestQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_request_dispatches_notification_job(): void
    {
        Queue::fake();

        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $viewingRequest = ViewingRequest::factory()->create([
            'buyer_id' => $buyer->id,
            'listing_id' => $listing->id,
            'status' => 'pending',
        ]);

        $this->actingAs($seller)
            ->patchJson(
                "/viewing-requests/{$viewingRequest->id}/decision",
                ['status' => 'confirmed']
            )
            ->assertStatus(200);

        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'status' => 'confirmed',
        ]);

        Queue::assertPushed(
            SendViewingRequestNotification::class,
            function (SendViewingRequestNotification $job) use ($buyer, $viewingRequest) {
                return $job->buyerId === $buyer->id
                    && $job->viewingRequestId === $viewingRequest->id
                    && $job->status === 'confirmed';
            }
        );

        Queue::assertPushed(
            SendViewingRequestNotification::class,
            1
        );

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $buyer->id,
            'type' => 'viewing_request',
        ]);
    }

    public function test_notification_job_creates_notification(): void
    {
        $buyer = User::factory()->create();

        $viewingRequest = ViewingRequest::factory()->create([
            'buyer_id' => $buyer->id,
            'status' => 'confirmed',
        ]);

        $job = new SendViewingRequestNotification(
            $buyer->id,
            $viewingRequest->id,
            'confirmed'
        );

        $job->handle();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'type' => 'viewing_request',
            'is_read' => false,
            'message' => "Вашу заявку №{$viewingRequest->id} на перегляд нерухомості підтверджено продавцем.",
        ]);
    }
}
