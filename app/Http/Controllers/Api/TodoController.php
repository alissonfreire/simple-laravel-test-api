<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TodoStoreRequest;
use App\Http\Responses\ApiResponse;
use App\Services\TodoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class TodoController extends Controller
{
    public function __construct(protected TodoService $todoService) {}

    #[OA\Get(
        path: '/todos',
        security: [['bearerAuth' => []]],
        summary: 'Returns a todo list',
        description: 'Returns all todos from logged user.',
        tags: ['todos'],
    )]
    #[OA\Response(
        response: 200,
        description: 'Todo listed successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'todos',
                            type: 'array',
                            items: new OA\Items(type: 'object', ref: '#/components/schemas/Todo')
                        ),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, ref: '#/components/schemas/UnauthorizedResponse')]
    public function index(Request $request): ApiResponse
    {
        $todos = $this->todoService->all($request->all());

        return ApiResponse::success(['todos' => $todos]);
    }

    #[OA\Post(
        path: '/todos',
        security: [['bearerAuth' => []]],
        summary: 'Create a new todo',
        description: 'Creates a todo with the provided details.',
        tags: ['todos'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'do something tomorrow at 10'),
                new OA\Property(property: 'description', type: 'string', example: 'remember to do something tomorrow at 10 am'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Todo created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'todo', ref: '#/components/schemas/Todo'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Form validation error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'fail'),
                new OA\Property(property: 'message', type: 'string', example: 'form validation error'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'title',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The title field is required.')
                        ),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, ref: '#/components/schemas/UnauthorizedResponse')]
    public function store(TodoStoreRequest $request): ApiResponse
    {
        $todo = $this->todoService->create($request->only(['title', 'description']));

        return ApiResponse::success(data: ['todo' => $todo], statusCode: 201);
    }

    #[OA\Get(
        path: '/todos/{id}',
        security: [['bearerAuth' => []]],
        summary: 'Get a todo by id',
        description: 'Get a existing todo by integer id.',
        tags: ['todos'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                description: 'The ID of the todo item'
            ),
        ],
    )]
    #[OA\Response(
        response: 200,
        description: 'Todo returned successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'todo',
                            ref: '#/components/schemas/Todo'
                        ),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 404, ref: '#/components/schemas/NotFoundResponse')]
    #[OA\Response(response: 401, ref: '#/components/schemas/UnauthorizedResponse')]
    public function show(int $todoId): ApiResponse
    {
        $todo = $this->todoService->findById($todoId);

        if (! $todo) {
            return ApiResponse::fail(
                errorMessage: 'not found error',
                errors: [],
                statusCode: 404
            );
        }

        return ApiResponse::success(['todo' => $todo]);
    }

    #[OA\Put(
        path: '/todos/{id}/done',
        security: [['bearerAuth' => []]],
        summary: 'Mark todo as done',
        description: 'Mark a existing todo as done.',
        tags: ['todos'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                description: 'The ID of the todo item'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Todo marked as done successfully',
                ref: '#/components/schemas/SuccessEmptyResponse'
            ),
            new OA\Response(
                response: 404,
                ref: '#/components/schemas/NotFoundResponse'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/schemas/UnauthorizedResponse'
            ),
        ]
    )]
    public function done(int $todoId): ApiResponse
    {
        $this->todoService->done($todoId);

        return ApiResponse::success([]);
    }

    #[OA\Put(
        path: '/todos/{id}/undone',
        security: [['bearerAuth' => []]],
        summary: 'Mark todo as undone',
        description: 'Mark a existing todo as undone.',
        tags: ['todos'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                description: 'The ID of the todo item'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Todo marked as undone successfully',
                ref: '#/components/schemas/SuccessEmptyResponse'
            ),
            new OA\Response(
                response: 404,
                ref: '#/components/schemas/NotFoundResponse'
            ),
        ]
    )]
    public function undone(int $todoId): ApiResponse
    {
        $this->todoService->undone($todoId);

        return ApiResponse::success([]);
    }

    #[OA\Delete(
        security: [['bearerAuth' => []]],
        path: '/todos',
        summary: 'Delete todo',
        description: 'Delete a todo given an id.',
        tags: ['todos'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'No content'
            ),
        ]
    )]
    public function destroy(int $todoId): Response
    {
        $this->todoService->delete($todoId);

        return response()->noContent();
    }
}
