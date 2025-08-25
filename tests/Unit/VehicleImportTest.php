<?php

namespace Tests\Unit;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VehicleImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests the main scenario: creates new vehicles, updates existing ones,
     * ignores invalid entries, and logs the success.
     */
    #[Test]
    public function it_successfully_imports_new_and_updates_existing_vehicles(): void
    {
        $existingVehicle = Vehicle::factory()->create([
            'board' => 'ABC-1234',
            'km' => 50000,
        ]);

        Http::fake([
            'https://hub.alpes.one/api/v1/integrator/export/1902' => Http::response($this->mockApiData(), 200),
        ]);

        Log::shouldReceive('info')->once()->with('Vehicle import successful.');
        Log::shouldReceive('warning')->once();

        $this->artisan('app:import-vehicles')
            ->expectsOutput('Vehicles imported successfully!')
            ->assertExitCode(0);

        $this->assertDatabaseCount('vehicles', 2);

        $this->assertDatabaseHas('vehicles', [
            'board' => 'DEF-5678',
            'brand' => 'Brand New',
            'price' => 75000.50,
        ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $existingVehicle->id,
            'board' => 'ABC-1234',
            'km' => 99999,
        ]);

        $this->assertDatabaseMissing('vehicles', [
            'brand' => 'Invalid Brand',
        ]);
    }

    /**
     * Tests if the command handles an API failure gracefully.
     */
    #[Test]
    public function it_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'https://hub.alpes.one/api/v1/integrator/export/1902' => Http::response(null, 500),
        ]);

        Log::shouldReceive('error')->once();

        $this->artisan('app:import-vehicles')
            ->expectsOutput('Failed to fetch vehicles from the API.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('vehicles', 0);
    }

    /**
     * Provides a mock dataset to simulate the API response.
     */
    private function mockApiData(): array
    {
        return [
            [
                "id" => 1,
                "type" => "carro",
                "brand" => "Brand Existing",
                "model" => "Updated Model",
                "version" => "1.0",
                "year" => [
                    "model" => "2025",
                    "build" => "2024"
                ],
                "optionals" => [],
                "doors" => 4,
                "board" => "ABC-1234",
                "chassi" => "CHASSI123",
                "transmission" => "Automática",
                "km" => 99999,
                "description" => "Updated description",
                "sold" => "0",
                "category" => "SUV",
                "url_car" => "url-updated",
                "price" => "120000.00",
                "color" => "Preto",
                "fuel" => "Flex",
                "fotos" => ["url1.jpg"],
                "created" => "2025-01-01 10:00:00",
                "updated" => "2025-08-25 10:00:00",
            ],
            [
                "id" => 2,
                "type" => "carro",
                "brand" => "Brand New",
                "model" => "New Model",
                "version" => "2.0",
                "year" => [
                    "model" => "2024",
                    "build" => "2024"
                ],
                "optionals" => [],
                "doors" => 2,
                "board" => "DEF-5678",
                "chassi" => "CHASSI456",
                "transmission" => "Manual",
                "km" => 100,
                "description" => "New description",
                "sold" => "1",
                "category" => "Hatch",
                "url_car" => "url-new",
                "price" => "75000.50",
                "color" => "Branco",
                "fuel" => "Gasolina",
                "fotos" => ["url2.jpg"],
                "created" => "2025-02-01 11:00:00",
                "updated" => "2025-08-25 11:00:00",
            ],
            [
                "id" => 3,
                "type" => "carro",
                "brand" => "Invalid Brand",
                "model" => "Invalid Model",
                "version" => "3.0",
                "year" => [
                    "model" => "2023",
                    "build" => "2023"
                ],
                "optionals" => [],
                "doors" => 5,
//                "board" => "GHI-9101",
                "chassi" => "CHASSI789",
                "transmission" => "CVT",
                "km" => 20000,
                "description" => "Invalid description",
                "sold" => "0",
                "category" => "Sedan",
                "url_car" => "url-invalid",
                "price" => "95000.00",
                "color" => "Cinza",
                "fuel" => "Diesel",
                "fotos" => ["url3.jpg"],
                "created" => "2025-03-01 12:00:00",
                "updated" => "2025-08-25 12:00:00",
            ],
        ];
    }
}
