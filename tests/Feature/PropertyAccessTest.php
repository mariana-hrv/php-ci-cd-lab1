<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyAccessTest extends TestCase
{
    use RefreshDatabase;

    // Гість не може створити нерухомість навіть із заголовком
    public function test_guest_cannot_create_property(): void
    {
        $response = $this->withHeaders([
            'X-Property-Agent' => 'demo-agent',
        ])->postJson('/properties', [
            'propertyType' => 'Квартира',
            'city' => 'Івано-Франківськ',
            'address' => 'вул. Галицька, 15',
            'area' => 75.5,
            'rooms' => 3,
        ]);

        $response->assertStatus(401);

        $this->assertDatabaseCount('properties', 0);
    }

    // Перевірка блокування запиту без заголовка
    public function test_request_without_property_agent_header_is_blocked(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/properties', [
            'propertyType' => 'Квартира',
            'city' => 'Івано-Франківськ',
            'address' => 'вул. Галицька, 15',
            'area' => 75.5,
            'rooms' => 3,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'access_denied',
                'message' => 'Доступ заборонено: відсутній заголовок X-Property-Agent.',
            ]);

        $this->assertDatabaseCount('properties', 0);
    }

    // Перевірка створення нерухомості в базі даних
    public function test_request_with_property_agent_header_creates_property(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withHeaders([
            'X-Property-Agent' => 'demo-agent',
        ])->postJson('/properties', [
            'propertyType' => 'Квартира',
            'city' => 'Івано-Франківськ',
            'address' => 'вул. Галицька, 15',
            'area' => 75.5,
            'rooms' => 3,
        ]);

        $response->assertStatus(201)
            ->assertHeader('X-Security-Policy', 'Enforced')
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('data.propertyType', 'Квартира')
            ->assertJsonPath('data.city', 'Івано-Франківськ');

        $this->assertDatabaseHas('properties', [
            'property_type' => 'Квартира',
            'city' => 'Івано-Франківськ',
            'address' => 'вул. Галицька, 15',
            'rooms' => 3,
        ]);

        $this->assertDatabaseCount('properties', 1);
    }
}
