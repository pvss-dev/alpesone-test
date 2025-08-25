<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class VehicleController extends Controller
{
    #[OA\Get(
        path: '/api/vehicles',
        summary: 'List all vehicles',
        security: [['bearerAuth' => []]],
        tags: ['Vehicles'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paged list of vehicles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Vehicle')
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function index(): JsonResponse
    {
        $vehicles = Vehicle::paginate(15);
        return response()->json($vehicles);
    }

    #[OA\Post(
        path: '/api/vehicles',
        summary: 'Creates a new vehicle',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/Vehicle')
        ),
        tags: ['Vehicles'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Vehicle created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Vehicle')
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $rules = Vehicle::getRules();
        $rules['board'] .= '|unique:vehicles,board';
        $rules['chassi'] .= '|unique:vehicles,chassi';

        $validatedData = $request->validate($rules);

        $vehicle = Vehicle::create($validatedData);
        return response()->json($vehicle, 201);
    }

    #[OA\Get(
        path: '/api/vehicles/{id}',
        summary: 'Displays a specific vehicle',
        security: [['bearerAuth' => []]],
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Vehicle data',
                content: new OA\JsonContent(ref: '#/components/schemas/Vehicle')
            ),
            new OA\Response(response: 404, description: 'Vehicle not found'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle);
    }

    #[OA\Put(
        path: '/api/vehicles/{id}',
        summary: 'Upgrades an existing vehicle',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/Vehicle')
        ),
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Vehicle updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Vehicle')
            ),
            new OA\Response(response: 404, description: 'Vehicle not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $rules = Vehicle::getRules();
        $rules['board'] .= '|' . Rule::unique('vehicles')->ignore($vehicle->getKey());
        $rules['chassi'] .= '|' . Rule::unique('vehicles')->ignore($vehicle->getKey());

        $validatableRules = array_map(fn($rule) => 'sometimes|' . $rule, $rules);

        $validatedData = $request->validate($validatableRules);

        $vehicle->update($validatedData);
        return response()->json($vehicle);
    }

    #[OA\Delete(
        path: '/api/vehicles/{id}',
        summary: 'Deletes a vehicle',
        security: [['bearerAuth' => []]],
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Vehicle successfully deleted'),
            new OA\Response(response: 404, description: 'Vehicle not found'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return response()->json(null, 204);
    }
}
