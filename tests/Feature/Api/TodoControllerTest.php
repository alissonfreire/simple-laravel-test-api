<?php

namespace Tests\Feature\Api;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TodoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testMustReturnsAllTodosFromUser(): void
    {
        // Arrange
        $user = User::factory()->create();

        $todos = Todo::factory(3)
            ->for($user)
            ->create();

        Sanctum::actingAs($user, []);

        // Act
        $response = $this->get('/api/todos');

        // Assert
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'data' => ['todos'],
        ]);

        ['status' => $status, 'data' => ['todos' => $responseTodos]] = $response->json();

        $this->assertEquals($status, 'success');

        $this->assertCount(3, $responseTodos);

        $todoIds = $todos->pluck('id');

        foreach ($responseTodos as $todo) {
            $this->assertContains($todo['id'], $todoIds);
            $this->assertEquals($todo['user_id'], $user->id);
        }
    }

    public function testMustCreateATodoWithValidData(): void
    {
        // Arrange
        $user = User::factory()->create();

        Sanctum::actingAs($user, []);

        $data = [
            'title' => 'do something tomorrow at 10',
            'description' => 'remember to do something tomorrow at 10 am',
        ];

        // Act
        $response = $this->post('/api/todos', $data);

        // Assert
        $response->assertStatus(201);

        $response->assertJsonStructure([
            'status',
            'data' => [
                'todo',
            ],
        ]);

        ['status' => $status, 'data' => ['todo' => $responseTodo]] = $response->json();

        $this->assertEquals($status, 'success');

        $this->assertEquals($responseTodo['title'], $data['title']);
        $this->assertEquals($responseTodo['description'], $data['description']);
        $this->assertEquals($responseTodo['user_id'], $user->id);

        $this->assertEquals($responseTodo['done'], false);
        $this->assertEquals($responseTodo['done_at'], null);

        $this->assertDatabaseHas('todos', $data);
    }

    public function testDontCreateATodoWithInvalidData(): void
    {
        // Arrange
        $user = User::factory()->create();

        Sanctum::actingAs($user, []);

        $data = [
            'titleee' => 'do something tomorrow at 10',
        ];

        // Act
        $response = $this->post('/api/todos', $data);

        // Assert
        $response->assertStatus(422);

        $response->assertJsonStructure([
            'status',
            'message',
            'errors',
        ]);

        $response->assertJsonFragment([
            'status' => 'fail',
            'message' => 'form validation error',
            'errors' => [
                'title' => [
                    'The title field is required.',
                ],
            ],
        ]);

        $this->assertDatabaseMissing('users', $data);
    }

    public function testMustReturnsNotFoundWhenTodoDoesntExists(): void
    {
        // Arrange
        $user = User::factory()->create();

        Sanctum::actingAs($user, []);

        // Act
        $response = $this->get('/api/todos/1000');

        // Assert
        $response->assertStatus(404);

        $response->assertJsonFragment([
            'status' => 'fail',
            'message' => 'not found error',
            'errors' => [],
        ]);
    }

    public function testMustReturnsATodoById(): void
    {
        // Arrange
        $user = User::factory()->create();

        $todo = Todo::factory()
            ->for($user)
            ->create();

        Sanctum::actingAs($user, []);

        // Act
        $response = $this->get('/api/todos/'.$todo->id);

        // Assert
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'data' => ['todo'],
        ]);

        ['status' => $status, 'data' => ['todo' => $responseTodo]] = $response->json();

        $this->assertEquals($status, 'success');

        $todoData = $todo->toArray();

        $response->assertJsonFragment($todoData, $responseTodo);
    }

    public function testShouldBeMarkATodoAsDone(): void
    {
        // Arrange
        $user = User::factory()->create();

        $todo = Todo::factory()
            ->for($user)
            ->create(['done' => false, 'done_at' => null]);

        Sanctum::actingAs($user, []);

        // Act
        $response = $this->put("/api/todos/{$todo->id}/done");

        // Assert
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'status' => 'success',
            'data' => [],
        ]);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'done' => true,
        ]);
    }

    public function testShouldBeMarkATodoAsUnDone(): void
    {
        // Arrange
        $user = User::factory()->create();

        $todo = Todo::factory()
            ->for($user)
            ->create(['done' => true, 'done_at' => Carbon::now()]);

        Sanctum::actingAs($user, []);

        // Act
        $response = $this->put("/api/todos/{$todo->id}/undone");

        // Assert
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'status' => 'success',
            'data' => [],
        ]);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'done' => false,
        ]);
    }

    public function testMustBeReturnsNotFoundErrorWhenTodoDoesntExists(): void
    {
        // Arrange
        $user = User::factory()->create();

        Sanctum::actingAs($user, []);

        // Act
        $response = $this->put('/api/todos/1000/done');

        // Assert
        $response->assertStatus(404);

        $response->assertJsonFragment([
            'status' => 'fail',
            'message' => 'not found error',
            'errors' => [],
        ]);
    }

    public function testShouldBeDeleteATodoById(): void
    {
        // Arrange
        $user = User::factory()->create();

        $todo = Todo::factory()
            ->for($user)
            ->create();

        Sanctum::actingAs($user, []);

        // Act
        $response = $this->delete("/api/todos/{$todo->id}");

        // Assert
        $response->assertNoContent(204);

        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }
}
