<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Vehicle',
    title: 'Vehicle',
    description: 'Model of a vehicle',
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 1),
        new OA\Property(property: 'type', type: 'string', example: 'carro'),
        new OA\Property(property: 'brand', type: 'string', example: 'Hyundai'),
        new OA\Property(property: 'model', type: 'string', example: 'CRETA'),
        new OA\Property(property: 'version', type: 'string', example: 'CRETA 16A ACTION'),
        new OA\Property(property: 'year_model', type: 'string', example: '2025'),
        new OA\Property(property: 'year_build', type: 'string', example: '2025'),
        new OA\Property(property: 'optionals', type: 'string', example: 'Informações extras', nullable: true),
        new OA\Property(property: 'doors', type: 'integer', example: 5),
        new OA\Property(property: 'board', type: 'string', example: 'BRA2E19'),
        new OA\Property(property: 'chassi', type: 'string', example: '4TUKBM8WFSJTH1635', nullable: true),
        new OA\Property(property: 'transmission', type: 'string', example: 'Automática'),
        new OA\Property(property: 'km', type: 'integer', example: 24208),
        new OA\Property(property: 'description', type: 'string', example: 'Carro em ótimo estado de conservação, único dono, revisões em dia.', nullable: true),
        new OA\Property(property: 'sold', type: 'boolean', example: false),
        new OA\Property(property: 'category', type: 'string', example: 'Carros'),
        new OA\Property(property: 'url_car', type: 'string', example: 'https://example.com/creta-16a-action.jpg'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 115900.00),
        new OA\Property(property: 'color', type: 'string', example: 'Branco'),
        new OA\Property(property: 'fuel', type: 'string', example: 'Flex'),
        new OA\Property(property: 'photos', type: 'array', items: new OA\Items(type: 'string'), example: ["https://example.s3.amazonaws.com/creta1.jpeg", "https://example.s3.amazonaws.com/creta2.jpeg"]),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', readOnly: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', readOnly: true),
    ]
)]
class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'brand',
        'model',
        'version',
        'year_model',
        'year_build',
        'optionals',
        'doors',
        'board',
        'chassi',
        'transmission',
        'km',
        'description',
        'sold',
        'category',
        'url_car',
        'price',
        'color',
        'fuel',
        'photos',
        'original_created_at',
        'original_updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'photos' => 'array',
        'sold' => 'boolean',
        'price' => 'decimal:2',
        'doors' => 'integer',
        'km' => 'integer'
    ];

    public static function getRules(): array
    {
        return [
            'type' => 'required|string|max:100',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'version' => 'required|string|max:100',
            'year_model' => 'required|string|digits:4',
            'year_build' => 'required|string|digits:4',
            'optionals' => 'nullable|string|max:255',
            'doors' => 'required|integer|min:1',
            'board' => 'required|string|min:7|max:8',
            'chassi' => 'nullable|string|max:255',
            'transmission' => 'required|string|max:50',
            'km' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'sold' => 'required|boolean',
            'category' => 'required|string|max:100',
            'url_car' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'color' => 'required|string|max:50',
            'fuel' => 'required|string|max:50',
            'photos' => 'required|array',
            'original_created_at' => 'nullable|date',
            'original_updated_at' => 'nullable|date',
        ];
    }
}
