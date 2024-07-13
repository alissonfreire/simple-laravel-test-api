<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TodoStoreRequest;
use App\Http\Responses\ApiResponse;
use App\Services\TodoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TodoController extends Controller
{
    public function __construct(protected TodoService $todoService) {}

    public function index(Request $request): ApiResponse
    {
        $todos = $this->todoService->all($request->all());

        return ApiResponse::success(['todos' => $todos]);
    }

    public function store(TodoStoreRequest $request): ApiResponse
    {
        $todo = $this->todoService->create($request->only(['title', 'description']));

        return ApiResponse::success(data: ['todo' => $todo], statusCode: 201);
    }

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

    public function done(int $todoId): ApiResponse
    {
        $this->todoService->done($todoId);

        return ApiResponse::success([]);
    }

    public function undone(int $todoId): ApiResponse
    {
        $this->todoService->undone($todoId);

        return ApiResponse::success([]);
    }

    public function destroy(int $todoId): Response
    {
        $this->todoService->delete($todoId);

        return response()->noContent();
    }
}
