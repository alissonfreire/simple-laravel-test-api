<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testMustRegisterAnUserWithValidData(): void
    {
        // Arrange
        $data = [
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => '123456789',
            'password_confirmation' => '123456789',
        ];

        // Act
        $response = $this->post('/api/auth/register', $data);

        // Assert
        $response->assertStatus(201);

        $response->assertJsonStructure([
            'status',
            'data' => [
                'user',
                'token',
            ],
        ]);

        ['status' => $status, 'data' => $responseData] = $response->json();

        $this->assertEquals($status, 'success');

        $this->assertEquals($responseData['user']['name'], 'user');
        $this->assertEquals($responseData['user']['email'], 'user@example.com');
        $this->assertNotContains('password', $responseData['user']);

        $this->assertNotNull($responseData['token']);

        $this->assertDatabaseHas('users', [
            'name' => 'user',
            'email' => 'user@example.com',
        ]);
    }

    public function testDontRegisterAnUserWithInvalidData(): void
    {
        // Arrange
        $data = [
            'user_name' => 'user',
            'E-mail' => 'user@example.com',
        ];

        // Act
        $response = $this->post('/api/auth/register', $data);

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
                'name' => [
                    'The name field is required.',
                ],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ],
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'user',
            'email' => 'user@example.com',
        ]);
    }

    public function testShouldMakeLoginOfExistentUser(): void
    {
        // Arrange
        User::factory()->create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('123456789'),
        ]);

        $data = ['email' => 'user@example.com', 'password' => '123456789'];

        // Act
        $response = $this->post('/api/auth/login', $data);

        // Assert
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'data' => [
                'user',
                'token',
            ],
        ]);

        ['status' => $status, 'data' => $responseData] = $response->json();

        $this->assertEquals($status, 'success');

        $this->assertEquals($responseData['user']['name'], 'user');
        $this->assertEquals($responseData['user']['email'], 'user@example.com');
        $this->assertNotContains('password', $responseData['user']);

        $this->assertNotNull($responseData['token']);
    }

    public function testMustBeReturnsUnauthorizedWhenUserDoesntExists(): void
    {
        // Arrange
        $data = ['email' => 'user@example.com', 'password' => '123456789'];

        // Act
        $response = $this->post('/api/auth/login', $data);

        // Assert
        $response->assertStatus(401);

        $response->assertJsonFragment([
            'status' => 'fail',
            'message' => 'unauthorized error',
            'errors' => [],
        ]);
    }

    public function testMustBeReturnsUnauthorizedWhenUserExistsButPasswordIsIncorrect(): void
    {
        // Arrange
        User::factory()->create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make(Str::random(32)),
        ]);

        $data = ['email' => 'user@example.com', 'password' => '123456789'];

        // Act
        $response = $this->post('/api/auth/login', $data);

        // Assert
        $response->assertStatus(401);

        $response->assertJsonFragment([
            'status' => 'fail',
            'message' => 'unauthorized error',
            'errors' => [],
        ]);
    }

    public function testMustBeReturnsLoggedUser(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('123456789'),
        ]);

        Sanctum::actingAs($user, []);

        // Act
        $response = $this->get('/api/auth/me');

        // Assert
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'data' => [
                'user',
            ],
        ]);

        ['status' => $status, 'data' => $responseData] = $response->json();

        $this->assertEquals($status, 'success');

        $this->assertEquals($responseData['user']['name'], 'user');
        $this->assertEquals($responseData['user']['email'], 'user@example.com');
        $this->assertNotContains('password', $responseData['user']);
    }

    public function testMustBeReturnsUnauthorizedWhenUserDoesntLoggedIn(): void
    {
        // Arrange

        // Act
        $response = $this->get('/api/auth/me', headers: ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);

        $response->assertJsonFragment([
            'status' => 'fail',
            'message' => 'unauthorized error',
            'errors' => [],
        ]);
    }

    public function testMustBeLogoutUser(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('123456789'),
        ]);

        Sanctum::actingAs($user, []);

        // Act
        $responseOne = $this->get('/api/auth/logout');

        // Assert
        $responseOne->assertNoContent(204);
    }
}
