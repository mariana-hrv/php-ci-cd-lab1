<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use App\Models\ViewingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewingRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    // Гість не може працювати із заявками
    public function test_guest_cannot_access_requests(): void
    {
        $this->getJson('/viewing-requests')
            ->assertStatus(401);

        $this->postJson('/viewing-requests', [])
            ->assertStatus(401);

        $this->putJson('/viewing-requests/1', [])
            ->assertStatus(401);

        $this->deleteJson('/viewing-requests/1')
            ->assertStatus(401);
    }

    // Покупець може створити заявку
    public function test_buyer_can_create_request(): void
    {
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create();

        $response = $this->actingAs($buyer)
            ->postJson('/viewing-requests', [
                'listing_id' => $listing->id,
                'preferred_date' => today()
                    ->addDays(3)
                    ->toDateString(),
                'preferred_time' => '14:00',
                'comment' => 'Хочу переглянути квартиру',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('data.buyer_id', $buyer->id)
            ->assertJsonPath('data.listing_id', $listing->id)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('viewing_requests', [
            'buyer_id' => $buyer->id,
            'listing_id' => $listing->id,
            'status' => 'pending',
        ]);
    }

    // Покупець бачить свої заявки з пов'язаними даними
    public function test_buyer_can_view_own_requests(): void
    {
        $buyer = User::factory()->create();

        $viewingRequest = ViewingRequest::factory()->create([
            'buyer_id' => $buyer->id,
        ]);

        $response = $this->actingAs($buyer)
            ->getJson('/viewing-requests');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.id',
                $viewingRequest->id
            )
            ->assertJsonPath(
                'data.0.listing.id',
                $viewingRequest->listing_id
            );

        $this->getJson(
            "/viewing-requests/{$viewingRequest->id}"
        )
            ->assertStatus(200)
            ->assertJsonPath(
                'data.listing.property.id',
                $viewingRequest->listing->property_id
            );
    }

    // Покупець не може переглядати чи змінювати чужу заявку
    public function test_buyer_cannot_access_another_request(): void
    {
        $owner = User::factory()->create();
        $otherBuyer = User::factory()->create();

        $viewingRequest = ViewingRequest::factory()->create([
            'buyer_id' => $owner->id,
            'status' => 'pending',
        ]);

        $this->actingAs($otherBuyer)
            ->getJson("/viewing-requests/{$viewingRequest->id}")
            ->assertStatus(404);

        $this->putJson(
            "/viewing-requests/{$viewingRequest->id}",
            ['comment' => 'Чужа зміна']
        )->assertStatus(404);

        $this->deleteJson(
            "/viewing-requests/{$viewingRequest->id}"
        )->assertStatus(404);

        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'buyer_id' => $owner->id,
            'status' => 'pending',
        ]);
    }

    // Покупець може редагувати заявку зі статусом pending
    public function test_buyer_can_update_pending_request(): void
    {
        $buyer = User::factory()->create();

        $viewingRequest = ViewingRequest::factory()->create([
            'buyer_id' => $buyer->id,
            'status' => 'pending',
        ]);

        $newDate = today()->addDays(5)->toDateString();

        $response = $this->actingAs($buyer)
            ->putJson(
                "/viewing-requests/{$viewingRequest->id}",
                [
                    'preferred_date' => $newDate,
                    'preferred_time' => '16:00',
                    'comment' => 'Оновлений час перегляду',
                ]
            );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'preferred_date' => $newDate,
            'comment' => 'Оновлений час перегляду',
            'status' => 'pending',
        ]);
    }

    // Покупець може скасувати заявку
    public function test_buyer_can_cancel_pending_request(): void
    {
        $buyer = User::factory()->create();

        $viewingRequest = ViewingRequest::factory()->create([
            'buyer_id' => $buyer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($buyer)
            ->deleteJson(
                "/viewing-requests/{$viewingRequest->id}"
            );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Запис залишається в БД, змінюється лише статус
        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'status' => 'cancelled',
        ]);
    }

    // Підтверджену заявку не можна змінити або скасувати
    public function test_confirmed_request_cannot_be_modified(): void
    {
        $buyer = User::factory()->create();

        $viewingRequest = ViewingRequest::factory()->create([
            'buyer_id' => $buyer->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($buyer)
            ->putJson(
                "/viewing-requests/{$viewingRequest->id}",
                ['comment' => 'Новий коментар']
            )->assertStatus(409);

        $this->deleteJson(
            "/viewing-requests/{$viewingRequest->id}"
        )->assertStatus(409);

        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'status' => 'confirmed',
        ]);
    }

    // Некоректні дата, час та ID оголошення відхиляються
    public function test_creation_rejects_invalid_data(): void
    {
        $buyer = User::factory()->create();

        $response = $this->actingAs($buyer)
            ->postJson('/viewing-requests', [
                'listing_id' => 999999,
                'preferred_date' => today()
                    ->subDays(2)
                    ->toDateString(),
                'preferred_time' => '25:99',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'listing_id',
                'preferred_date',
                'preferred_time',
            ]);

        $this->assertDatabaseCount('viewing_requests', 0);
    }

    // Продавець бачить лише заявки на свої оголошення
    public function test_seller_can_view_incoming_requests(): void
    {
        $seller = User::factory()->create();
        $otherSeller = User::factory()->create();

        $ownListing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $otherListing = Listing::factory()->create([
            'seller_id' => $otherSeller->id,
        ]);

        $ownRequest = ViewingRequest::factory()->create([
            'listing_id' => $ownListing->id,
            'status' => 'pending',
        ]);

        ViewingRequest::factory()->create([
            'listing_id' => $otherListing->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)
            ->getJson('/viewing-requests/incoming');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownRequest->id)
            ->assertJsonPath(
                'data.0.listing.id',
                $ownListing->id
            );
    }

    // Продавець може підтвердити заявку
    public function test_seller_can_confirm_request(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $viewingRequest = ViewingRequest::factory()->create([
            'listing_id' => $listing->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)
            ->patchJson(
                "/viewing-requests/{$viewingRequest->id}/decision",
                ['status' => 'confirmed']
            );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'status' => 'confirmed',
        ]);
    }

    // Продавець може відхилити заявку
    public function test_seller_can_reject_request(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $viewingRequest = ViewingRequest::factory()->create([
            'listing_id' => $listing->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)
            ->patchJson(
                "/viewing-requests/{$viewingRequest->id}/decision",
                ['status' => 'rejected']
            );

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'status' => 'rejected',
        ]);
    }

    // Інший продавець не може обробляти чужу заявку
    public function test_seller_cannot_decide_another_sellers_request(): void
    {
        $seller = User::factory()->create();
        $otherSeller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $viewingRequest = ViewingRequest::factory()->create([
            'listing_id' => $listing->id,
            'status' => 'pending',
        ]);

        $this->actingAs($otherSeller)
            ->patchJson(
                "/viewing-requests/{$viewingRequest->id}/decision",
                ['status' => 'confirmed']
            )
            ->assertStatus(404);

        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'status' => 'pending',
        ]);
    }

    // Некоректний статус та повторна обробка заборонені
    public function test_seller_cannot_make_invalid_decision(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $viewingRequest = ViewingRequest::factory()->create([
            'listing_id' => $listing->id,
            'status' => 'confirmed',
        ]);

        $url = "/viewing-requests/{$viewingRequest->id}/decision";

        $this->actingAs($seller)
            ->patchJson($url, [
                'status' => 'unknown',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->patchJson($url, [
            'status' => 'rejected',
        ])->assertStatus(409);

        $this->assertDatabaseHas('viewing_requests', [
            'id' => $viewingRequest->id,
            'status' => 'confirmed',
        ]);
    }
}
