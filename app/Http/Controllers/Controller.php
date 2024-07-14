<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Simple Todo Rest API',
    version: '1.0.0',
    description: "An API for managing user's todos"
)]
#[OA\Server(
    url: 'http://localhost/api',
    description: 'Simple Todo Rest API'
)]

#[OA\SecurityScheme(type: 'http', scheme: 'bearer', securityScheme: 'bearerAuth')]

#[OA\Schema(
    schema: 'SuccessEmptyResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string'), example: []),
    ]
)]
#[OA\Schema(
    schema: 'UnauthorizedResponse',
    type: 'object',
    description: 'Unauthorized error',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'fail'),
        new OA\Property(property: 'message', type: 'string', example: 'unauthorized error'),
        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string'), example: []),
    ]
)]
#[OA\Schema(
    schema: 'NotFoundResponse',
    type: 'object',
    description: 'Not found error',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'fail'),
        new OA\Property(property: 'message', type: 'string', example: 'not found error'),
        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string'), example: []),
    ]
)]

abstract class Controller
{
    //
}
