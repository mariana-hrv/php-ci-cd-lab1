<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PropertyAccessTest extends TestCase
{
    public function test_request_without_property_agent_header_is_blocked(): void
    {
        $response = $this->postJson('/properties', [
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
    }

    public function test_request_with_property_agent_header_creates_property(): void
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

        $response->assertStatus(201)
            ->assertHeader('X-Security-Policy', 'Enforced')
            ->assertJson([
                'status' => 'created',
                'data' => [
                    'id' => 3,
                    'propertyType' => 'Квартира',
                    'city' => 'Івано-Франківськ',
                    'address' => 'вул. Галицька, 15',
                    'area' => 75.5,
                    'rooms' => 3,
                ],
            ]);
    }
}
