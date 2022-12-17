<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private string $loginRoute;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginRoute = route('api.v1.login');
    }


    /** @test */
    public function can_login()
    {
        $user = $this->createUser(['password' => bcrypt($password = 'password')]);

        $response = $this->postJson($this->loginRoute, [
            'email' => $user->email,
            'password' => $password
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'access_token'
                ]
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]
            ]);

        $this->assertCount(1, $user->tokens);
    }

    /** @test */
    public function cannot_login_with_unregistered_email()
    {
        $this->postJson($this->loginRoute, [
            'email' => 'not-registered@example.com',
            'password' => 'password'
        ])
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    /** @test */
    public function cannot_login_with_wrong_password()
    {
        $user = $this->createUser(['password' => bcrypt('password')]);

        $this->postJson($this->loginRoute, [
            'email' => $user->email,
            'password' => 'wrong-password'
        ])
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    /** @test */
    public function cannot_attempt_to_login_more_than_3_times_in_1_minute()
    {

        for ($i = 0; $i < 3; $i++) {
            if ($i < 3) {
                $this->postJson($this->loginRoute, [
                    'email' => 'not-registered@example.com',
                    'password' => 'wrong-password'
                ]);
            }
        }

        $this->postJson($this->loginRoute, [
            'email' => 'not-registered@example.com',
            'password' => 'wrong-password'
        ])
            ->assertStatus(429)
            ->assertJsonStructure(['message']);
    }

    /** @test */
    public function login_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(AuthController::class, 'login', LoginRequest::class);
    }

    /** @test */
    public function login_request_has_the_correct_rules()
    {
        $this->assertValidationRules([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string']
        ], (new LoginRequest())->rules());
    }

    /** @test */
    public function return_validation_error_if_all_fields_are_empty()
    {
        $response = $this->postJson($this->loginRoute, [
            'email' => null,
            'password' => null
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['*' => []]
            ])
            ->assertJsonValidationErrors(['email', 'password']);
    }


    private function createUser(array $params = []): User
    {
        return User::factory()->create($params);
    }
}
