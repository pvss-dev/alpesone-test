<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Info(
        version: "1.0.0",
        description: "API for the Junior Back-End Developer technical test",
        title: "Alpes One API - Technical Test"
    ),
    OA\Server(
        url: 'http://localhost',
        description: 'Local Development Server'
    ),
    OA\SecurityScheme(
        securityScheme: 'bearerAuth',
        type: 'http',
        description: "Authentication via Bearer Token (Sanctum)",
        scheme: 'bearer'
    ),
    OA\Tag(
        name: 'Vehicles',
        description: 'Endpoints for managing vehicles'
    )
]
abstract class Controller
{
    //
}
