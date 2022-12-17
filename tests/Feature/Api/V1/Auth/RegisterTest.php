<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private string $registerRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerRoute = route('api.v1.register');
        $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function can_register()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123'
        ];

        $response = $this->postJson($this->registerRoute, $payload);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'name' => $payload['name'],
                        'email' => $payload['email']
                    ]
                ]
            ])->dump();

        $this->assertDatabaseHas('users', ['name' => $payload['name'], 'email' => $payload['email']]);

        $user = User::whereEmail($payload['email'])->first();

        $this->assertTrue(Hash::check($payload['password'], $user->password));
        $this->assertTrue($user->hasRole('user'));
    }

    /** @test */
    public function register_uses_the_correct_form_request()
    {
        $this->assertActionUsesFormRequest(AuthController::class, 'register', RegisterRequest::class);
    }

    /** @test */
    public function register_request_has_the_correct_rules()
    {
        $this->assertValidationRules([
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'string', 'email', 'min:5', 'max:130'],
            'password' => ['required', 'string', 'min:6', 'max:25', Password::min(6)->mixedCase()->numbers(), 'confirmed'],
        ], (new RegisterRequest())->rules());
    }

    /** @test */
    public function return_validation_error_if_all_fields_are_empty()
    {
        $payload = [
            'name' => null,
            'email' => null,
            'password' => null,
            'password_confirmation' => null
        ];

        $response = $this->postJson($this->registerRoute, $payload);
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['*' => []]
            ])
            ->assertJsonValidationErrors(['name', 'email', 'password']);

    }



}
