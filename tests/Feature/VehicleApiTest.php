<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VehicleApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/vehicles';
    private User $user;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_denies_access_to_unauthenticated_users(): void
    {
        $this->getJson($this->baseUrl)->assertUnauthorized();
    }

    #[Test]
    public function it_can_get_a_paginated_list_of_vehicles(): void
    {
        Vehicle::factory()->count(5)->create();

        $response = $this->actingAs($this->user)->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function it_can_create_a_vehicle_with_valid_data(): void
    {
        $vehicleData = Vehicle::factory()->make()->toArray();

        $response = $this->actingAs($this->user)->postJson($this->baseUrl, $vehicleData);

        $response->assertStatus(201)
            ->assertJsonFragment(['board' => $vehicleData['board']]);
    }

    #[Test]
    public function it_cannot_create_a_vehicle_with_invalid_data(): void
    {
        $vehicleData = Vehicle::factory()->make(['brand' => null])->toArray();

        $response = $this->actingAs($this->user)->postJson($this->baseUrl, $vehicleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['brand']);
    }

    #[Test]
    public function it_cannot_create_a_vehicle_with_a_duplicate_board(): void
    {
        Vehicle::factory()->create(['board' => 'XYZ-1234']);
        $newData = Vehicle::factory()->make(['board' => 'XYZ-1234'])->toArray();

        $response = $this->actingAs($this->user)->postJson($this->baseUrl, $newData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['board']);
    }

    #[Test]
    public function it_can_show_a_specific_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->user)->getJson($this->baseUrl . '/' . $vehicle->getKey());

        $response->assertStatus(200)
            ->assertJson(['id' => $vehicle->getKey()]);
    }

    #[Test]
    public function it_returns_a_404_if_showing_a_non_existent_vehicle(): void
    {
        $response = $this->actingAs($this->user)->getJson($this->baseUrl . '/9999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();
        $updateData = ['brand' => 'Updated Brand'];

        $response = $this->actingAs($this->user)->putJson($this->baseUrl . '/' . $vehicle->getKey(), $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['brand' => 'Updated Brand']);
    }

    #[Test]
    public function it_can_delete_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson($this->baseUrl . '/' . $vehicle->getKey());

        $response->assertStatus(204);
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->getKey()]);
    }
}
