<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Simple Todo Rest API',
    version: '1.0.0',
    description: "An API for managing user's todos"
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Simple Todo Rest API'
)]

#[OA\SecurityScheme(type: 'http', scheme: 'bearer', securityScheme: 'bearerAuth')]
abstract class Controller
{
    //
}
