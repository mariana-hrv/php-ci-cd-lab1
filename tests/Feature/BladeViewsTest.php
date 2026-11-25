<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BladeViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_displayed(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertViewIs('home');
    }

    public function test_property_catalog_displays_database_records(): void
    {
        Property::factory()->create([
            'city' => 'Ivano-Frankivsk',
            'address' => 'Test Street, 25',
        ]);

        $this->get(route('properties.index'))
            ->assertOk()
            ->assertViewIs('properties.index')
            ->assertSee('Ivano-Frankivsk')
            ->assertSee('Test Street, 25');
    }

    public function test_listing_catalog_displays_related_data(): void
    {
        $property = Property::factory()->create([
            'address' => 'Test Street, 50',
        ]);

        $seller = User::factory()->create([
            'name' => 'Test Seller',
        ]);

        Listing::factory()->create([
            'property_id' => $property->id,
            'seller_id' => $seller->id,
        ]);

        $this->get(route('listings.index'))
            ->assertOk()
            ->assertViewIs('listings.index')
            ->assertSee('Test Street, 50')
            ->assertSee('Test Seller');
    }

    public function test_property_form_displays_validation_errors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('properties.create'))
            ->post(route('properties.store-form'), [
                'propertyType' => 'apartment',
                'city' => 'Ivano-Frankivsk',
                'address' => '',
                'area' => -50,
                'rooms' => 0,
            ])
            ->assertRedirect(route('properties.create'))
            ->assertSessionHasErrors([
                'address',
                'area',
                'rooms',
            ]);

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_property_can_be_created_using_html_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('properties.store-form'), [
                'propertyType' => 'apartment',
                'city' => 'Ivano-Frankivsk',
                'address' => 'Test Street, 25',
                'area' => 75.50,
                'rooms' => 3,
            ])
            ->assertRedirect(route('properties.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'property_type' => 'apartment',
            'address' => 'Test Street, 25',
            'rooms' => 3,
        ]);
    }

    public function test_listing_edit_form_contains_current_values(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
            'description' => 'Test listing description',
        ]);

        $this->actingAs($seller)
            ->get(route('listings.edit', $listing->id))
            ->assertOk()
            ->assertViewIs('listings.edit')
            ->assertSee('Test listing description');
    }

    public function test_seller_can_update_listing_using_html_form(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($seller)
            ->post(route('listings.update', $listing->id), [
                '_method' => 'PUT',
                'price' => 82000,
                'description' => 'Updated description',
                'status' => 'published',
            ])
            ->assertRedirect(route('listings.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'price' => 82000,
            'description' => 'Updated description',
        ]);
    }

    public function test_seller_can_delete_own_listing(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($seller)
            ->post(route('listings.destroy', $listing->id), [
                '_method' => 'DELETE',
            ])
            ->assertRedirect(route('listings.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('listings', [
            'id' => $listing->id,
        ]);
    }

    public function test_user_cannot_edit_another_sellers_listing(): void
    {
        $user = User::factory()->create();

        $listing = Listing::factory()->create();

        $this->actingAs($user)
            ->get(route('listings.edit', $listing->id))
            ->assertForbidden();
    }

    public function test_regular_user_cannot_access_admin_user_list(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_profile_can_be_updated_without_changing_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated User',
                'email' => $user->email,
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => $user->email,
        ]);
    }

    public function test_profile_rejects_email_belonging_to_another_user(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $anotherUser->email,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
