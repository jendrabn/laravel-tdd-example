<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private string $logoutRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logoutRoute = route('api.v1.logout');
    }


    /** @test */
    public function can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson($this->logoutRoute)
            ->assertStatus(204);

        $this->assertEmpty($user->tokens);
    }

    /** @test */
    public function non_authenticated_user_cannot_logout()
    {
        $this->postJson($this->logoutRoute)
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }
}
