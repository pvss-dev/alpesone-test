<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImportVehicles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-vehicles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import vehicles from the Alpes One API';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $response = Http::get('https://hub.alpes.one/api/v1/integrator/export/1902');

            if ($response->successful()) {
                $vehicles = $response->json();
                $this->importData($vehicles);
                $this->newLine();
                $this->info('Vehicles imported successfully!');
                $this->newLine();
                Log::info('Vehicle import successful.');
            } else {
                $this->newLine();
                $this->error('Failed to fetch vehicles from the API.');
                $this->newLine();
                Log::error('Failed to fetch vehicles from API.', ['status' => $response->status()]);
            }
        } catch (Exception $e) {
            $this->newLine();
            $this->error('An error occurred: ' . $e->getMessage());
            $this->newLine();
            Log::critical('Vehicle import failed.', ['exception' => $e]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function importData(array $vehicles): void
    {
        $apiRules = [
            'id' => 'required|integer',
            'type' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'version' => 'required|string',
            'year' => 'required|array',
            'year.model' => 'required|string|digits:4',
            'year.build' => 'required|string|digits:4',
            'optionals' => 'present|array',
            'doors' => 'required|integer',
            'board' => 'required|string',
            'chassi' => 'present|nullable|string',
            'transmission' => 'required|string',
            'km' => 'required|integer',
            'description' => 'present|nullable|string',
            'sold' => 'required|in:0,1',
            'category' => 'required|string',
            'url_car' => 'required|string',
            'price' => 'required|numeric',
            'color' => 'required|string',
            'fuel' => 'required|string',
            'fotos' => 'required|array',
            'created' => 'required|date',
            'updated' => 'nullable|date'
        ];

        foreach ($vehicles as $vehicleData) {
            $validator = Validator::make($vehicleData, $apiRules);

            if ($validator->fails()) {
                Log::warning('Invalid vehicle data from API, skipping.', [
                    'id' => $vehicleData['id'] ?? 'N/A',
                    'errors' => $validator->errors()
                ]);
                continue;
            }

            $validated = $validator->validated();

            Vehicle::updateOrCreate(
                ['board' => $validated['board']],
                [
                    'type' => $validated['type'],
                    'brand' => $validated['brand'],
                    'model' => $validated['model'],
                    'version' => $validated['version'],
                    'year_model' => $validated['year']['model'],
                    'year_build' => $validated['year']['build'],
                    'optionals' => json_encode($validated['optionals']),
                    'doors' => $validated['doors'],
                    'board' => $validated['board'],
                    'chassi' => $validated['chassi'] ?: null,
                    'transmission' => $validated['transmission'],
                    'km' => $validated['km'],
                    'description' => $validated['description'],
                    'sold' => $validated['sold'] === '1',
                    'category' => $validated['category'],
                    'url_car' => $validated['url_car'],
                    'price' => $validated['price'],
                    'color' => $validated['color'],
                    'fuel' => $validated['fuel'],
                    'photos' => $validated['fotos'],
                    'original_created_at' => $validated['created'],
                    'original_updated_at' => $validated['updated']
                ]
            );
        }
    }
}
